<?php

namespace App\Services;

use App\Models\PredioFisico;
use App\Models\PredioFisicoAvaluo;
use App\Models\Construccion;
use App\Models\ValorUnitarioEdificacion;
use App\Models\ValorObraComplementaria;
use App\Models\ArancelUrbano;
use App\Models\ArancelRustico;
use App\Models\AnioFiscal;
use App\Models\Depreciacion;

class CalculoAutoavaluoService
{
    protected PredioFisico $predio;
    protected ?PredioFisicoAvaluo $avaluo = null;
    protected int $anio;
    protected int $anioId;
    protected float $factorOficializacion;
    protected string $zona;   // 'costa', 'sierra', 'selva'
    protected string $ubigeo; // 6 dígitos

    // TEMPORAL: $logDebug
    private bool $logDebug = false;

    public function __construct(PredioFisico $predio, int $anio = null)
    {
        $this->predio = $predio;
        $this->anio = $anio ?? now()->year;

        // Cargar relación Tenant si falta
        if (!$predio->relationLoaded('tenant')) {
            $predio->load('tenant');
        }

        // --- RESOLUCIÓN DEL AVALÚO VIGENTE ---
        $this->resolveAvaluo($this->anio);

        // --- VALIDACIÓN DE DATOS ESENCIALES ---
        // Si faltan datos críticos, lanzamos excepción para informar al usuario (requisito explícito)
        $this->validateAvaluoData();

        // Fallback Crítico: Si aún así es null (ej: predio nuevo sin avalúo generado), 
        // evitamos error fatal inicializando un objeto vacío o manejando el null en los métodos.
        // Para este servicio, dejaremos que $this->avaluo sea null y retornaremos ceros en calcularTotal().

        // Datos del Municipio (Tenant)
        $this->zona = $predio->tenant->zona_geografica ?? 'selva';
        $this->ubigeo = $predio->tenant->ubigeo ?? '000000';

        // Obtener el ID del Año Fiscal una sola vez para optimizar
        $this->anioId = AnioFiscal::where('anio', $this->anio)->value('id') ?? 0;

        // Obtener el Factor de Oficialización una sola vez para optimizar
        $this->factorOficializacion = AnioFiscal::where('id', $this->anioId)->value('factor_oficializacion') ?? 0;

        // TEMPORAL: logDebug
        $this->logDebug = true;
    }

    public function calcularTotal(): array
    {
        // Validamos que exista el año fiscal configurado y el avalúo
        if ($this->anioId === 0 || !$this->avaluo) {
            $this->logCalculation("❌ Cálculo abortado: Falta Año Fiscal ($this->anioId) o Avalúo (" . ($this->avaluo ? 'OK' : 'NULL') . ")");
            return $this->retornarCeros();
        }

        // TEMPORAL: Mensaje de depuración
        $this->logCalculation(">>> INICIO CÁLCULO AUTOAVALÚO [Predio: {$this->predio->id}] Año: $this->anio");
        // ---------------------------------

        $valorTerreno = $this->calcularTerreno();
        $valorConstruccion = $this->calcularConstrucciones();
        $valorObras = $this->calcularObrasComplementarias();

        $total = $valorTerreno + $valorConstruccion + $valorObras;

        // TEMPORAL: Mensaje de depuración
        $this->logCalculation("<<< FIN CÁLCULO. Total: " . number_format($total, 2) . " (T: " . number_format($valorTerreno, 2) . " + C: " . number_format($valorConstruccion, 2) . " + O: " . number_format($valorObras, 2) . ")");
        // ---------------------------------

        return [
            'valor_terreno' => round($valorTerreno, 2),
            'valor_construccion' => round($valorConstruccion, 2),
            'valor_obras' => round($valorObras, 2),
            'total_autoavaluo' => round($total, 2),
        ];
    }

    protected function calcularTerreno(): float
    {
        $area = $this->avaluo->area_terreno;

        $valorArancel = 0;

        // TEMPORAL: Mensaje de depuración
        $this->logCalculation("    Tipo de predio: {$this->predio->tipo_predio}");

        // Usamos los datos del Avaluo, no del PredioFisico (excepto el tipo_predio)
        if ($this->predio->tipo_predio === 'urbano') {
            // Lógica Urbana EXACTA
            $valorArancel = ArancelUrbano::where('anio_fiscal_id', $this->anioId)
                ->where('ubigeo_distrito', $this->ubigeo)
                ->where('tipo_calzada', $this->avaluo->tipo_calzada)
                ->where('ancho_via', $this->avaluo->ancho_via)
                ->where('tiene_agua', $this->avaluo->tiene_agua)
                ->where('tiene_desague', $this->avaluo->tiene_desague)
                ->where('tiene_luz', $this->avaluo->tiene_luz)
                ->value('valor_arancel');

            // TEMPORAL: Mensaje de depuración
            $this->logCalculation(
                "    - Ubigeo: {$this->ubigeo} \n" .
                "    - Tipo Calzada: {$this->avaluo->tipo_calzada} \n" .
                "    - Ancho Via: {$this->avaluo->ancho_via} \n" .
                "    - Agua: {$this->avaluo->tiene_agua} \n" .
                "    - Desague: {$this->avaluo->tiene_desague} \n" .
                "    - Luz: {$this->avaluo->tiene_luz}"
            );
            // ---------------------------------
        } else {
            // Lógica Rústica EXACTA
            $ubigeoProv = substr($this->ubigeo, 0, 4);

            $valorArancel = ArancelRustico::where('anio_fiscal_id', $this->anioId)
                ->where('ubigeo_provincia', $ubigeoProv)
                ->where('grupo_tierras', $this->avaluo->grupo_tierras)
                ->where('distancia', $this->avaluo->distancia)
                ->where('calidad_agrologica', $this->avaluo->calidad_agrologica)
                ->value('valor_arancel');

            // convertir área (m²) en has para aplicar el valor unitario de arancel rústico que se entrega en has
            $area = $area / 10000;

            // TEMPORAL: Mensaje de depuración
            $this->logCalculation(
                "    - Ubigeo: {$this->ubigeo} \n" .
                "    - Grupo Tierras: {$this->avaluo->grupo_tierras} \n" .
                "    - Distancia: {$this->avaluo->distancia} \n" .
                "    - Calidad Agrologica: {$this->avaluo->calidad_agrologica} \n" .
                "    - Área: {$area} has"
            );
            // ---------------------------------
        }

        $total = $area * ($valorArancel ?? 0);

        // TEMPORAL: Mensaje de depuración
        $unidad = $this->predio->tipo_predio === 'urbano' ? 'm2' : 'has';
        $this->logCalculation("   [Terreno] Area: $area $unidad * Arancel: " . ($valorArancel ?? 0) . " = $total");
        // ---------------------------------

        return $total;
    }

    protected function calcularConstrucciones(): float
    {
        $totalConstruccion = 0;

        // 1. Obtener TODAS las versiones de construcciones asociadas (históricas y actuales)
        // Ignoramos el GlobalScope 'active' para ver el historial completo.
        // Agrupamos por 'track_id' para procesar la línea de tiempo de cada piso/sección.
        $gruposConstrucciones = Construccion::withoutGlobalScope('active')
            ->where('predio_fisico_id', $this->predio->id)
            ->get()
            ->groupBy('track_id');

        $fechaObjetivo = \Carbon\Carbon::create($this->anio, 1, 1);

        foreach ($gruposConstrucciones as $trackId => $versiones) {

            // --- Lógica de Selección Histórica Retroactiva ---

            // Intento 1: Exacto (Vigente en la fecha objetivo)
            // valid_from <= Objetivo AND (valid_to >= Objetivo OR valid_to IS NULL)
            $piso = $versiones->first(function ($v) use ($fechaObjetivo) {
                return $v->valid_from <= $fechaObjetivo &&
                    ($v->valid_to === null || $v->valid_to >= $fechaObjetivo);
            });

            // Intento 2 (Fallback): Futuro más cercano
            // (Para cubrir huecos históricos)
            if (!$piso) {
                $piso = $versiones->where('valid_from', '>', $fechaObjetivo)
                    ->sortBy('valid_from')
                    ->first();
            }

            // Intento 3 (Fallback Final): El Activo
            if (!$piso) {
                $piso = $versiones->where('is_active', true)->first();
            }

            if (!$piso) {
                $this->logCalculation("   [Construcción] ⚠️ No se encontró versión válida para track_id $trackId");
                continue; // Si no encontramos versión viable, saltamos.
            }

            // --- Inicio Cálculo Original con $piso seleccionado ---

            // 1. Sumatoria de Valores Unitarios (Muros + Techos...)
            $valorUnitarioM2 = $this->obtenerValorUnitarioTotal($piso);

            // 2. Factor de Depreciación
            $porcentajeDepr = 0;

            if ($piso->mandato_depreciacion_manual !== null) {
                $porcentajeDepr = $piso->mandato_depreciacion_manual;
            } else {
                // Calculamos la edad antual del predio ($antiguedad)
                $antiguedad = $this->anio - $piso->anio_construccion;
                // Buscamos el valor exacto en la tabla de depreciación
                $porcentajeDepr = Depreciacion::buscar(
                    $piso->material_estructural,
                    $piso->uso_especifico,
                    $piso->estado_conservacion,
                    $antiguedad
                ) ?? 0;
            }

            $factorDepreciacion = (100 - $porcentajeDepr) / 100;

            // 3. Fórmula: Area * Valor * (1 - Depreciación)
            $valorPiso = $piso->area_construida * $valorUnitarioM2 * $factorDepreciacion;

            $this->logCalculation("   [Construcción] Piso {$piso->nro_piso} (Ver. {$piso->version}): Área {$piso->area_construida} * ValorU $valorUnitarioM2 * FactorDep " . number_format($factorDepreciacion, 2) . " = $valorPiso");

            $totalConstruccion += $valorPiso;
        }

        return $totalConstruccion;
    }

    protected function calcularObrasComplementarias(): float
    {
        $total = 0;

        // Optimización: Traer todos los valores del año y zona en una sola consulta
        $valoresReferenciales = ValorObraComplementaria::where('anio_fiscal_id', $this->anioId)
            ->where('zona_geografica', $this->zona)
            ->pluck('valor', 'catalogo_obra_complementaria_id');

        // Iteramos sobre la relación (pivot) predio_obras_complementarias
        foreach ($this->predio->obrasComplementarias as $obra) {
            $cantidad = $obra->pivot->cantidad;

            // Buscamos el valor en la colección cargada (O(1))
            $valorUnitario = $valoresReferenciales[$obra->id] ?? 0;

            // NOTA: Las obras complementarias también se deprecian.
            // Por simplicidad inicial, calculamos valor directo. 
            // Para depreciar obras, necesitaríamos una tabla de depreciación específica para instalaciones.

            $subtotal = ($cantidad * $valorUnitario);
            $total += $subtotal;

            // TEMPORAL: Mensaje de depuración
            $this->logCalculation("   [Obra] {$obra->descripcion} (ID: {$obra->id}): Cant $cantidad * Unit $valorUnitario = $subtotal");
            // ---------------------------------
        }
        // Actualiza el valor al factor de oficialización del año fiscal
        $total = $this->factorOficializacion * $total;

        return $total;
    }

    /**
     * Esta es la función clave que consultaste.
     * Busca fila por fila según tu estructura 'valor_unitario_edificacions'.
     */
    protected function obtenerValorUnitarioTotal(Construccion $piso): float
    {
        $totalM2 = 0.00;

        // Mapeo: [Columna en tabla construcciones] => [Valor en columna 'componente' de precios]
        // Asumimos que en tu CSV de carga usaste estos mismos nombres para 'componente'.
        $componentes = [
            'muros_columnas' => $piso->muros_columnas,             // 
            'techos' => $piso->techos,                     // 
            'pisos' => $piso->pisos,                      // 
            'puertas_ventanas' => $piso->puertas_ventanas,           // 
            'revestimientos' => $piso->revestimientos,             // 
            'banos' => $piso->banos,                      // 
            'inst_electricas_sanitarias' => $piso->inst_electricas_sanitarias, // 
        ];

        // Traemos TODOS los precios de la zona y año en una sola consulta (Eager Loading)
        // Esto evita hacer 7 consultas a la BD por cada piso.
        $preciosDelAnio = ValorUnitarioEdificacion::where('anio_fiscal_id', $this->anioId) // 
            ->where('zona_geografica', $this->zona) // 
            ->get();

        foreach ($componentes as $nombreComponente => $letraCategoria) {
            if (empty($letraCategoria))
                continue; // Si es null o vacío, saltar

            // Buscamos en la colección cargada en memoria
            $precioEncontrado = $preciosDelAnio
                ->where('componente', $nombreComponente)
                ->where('categoria', $letraCategoria)
                ->first();

            if ($precioEncontrado) {
                $totalM2 += $precioEncontrado->valor;
            }
        }

        return $totalM2;
    }

    protected function retornarCeros(): array
    {
        return [
            'valor_terreno' => 0,
            'valor_construccion' => 0,
            'valor_obras' => 0,
            'total_autoavaluo' => 0
        ];
    }

    /**
     * Resuelve qué versión del avalúo (PredioFisicoAvaluo) usar.
     * Regla 1: Vigente al 1 de Enero del año fiscal.
     * Regla 2 (Fallback): El siguiente vigente en el futuro más cercano.
     */
    private function resolveAvaluo(int $anio): void
    {
        $fechaObjetivo = \Carbon\Carbon::create($anio, 1, 1);

        // 1. Buscar coincidencia exacta (Vigente en Fecha Objetivo)
        $this->avaluo = $this->predio->predioFisicoAvaluos()
            ->withoutGlobalScope('active')
            ->whereDate('valid_from', '<=', $fechaObjetivo)
            ->where(function ($query) use ($fechaObjetivo) {
                $query->whereDate('valid_to', '>=', $fechaObjetivo)
                    ->orWhereNull('valid_to');
            })
            ->first();

        // 2. Fallback: Buscar "Próximo Vigente Futuro"
        // Si no existía existía avalúo el 1 de Enero (ej: predio creado en Mayo),
        // tomamos el primero que aparezca después.
        if (!$this->avaluo) {
            $this->avaluo = $this->predio->predioFisicoAvaluos()
                ->withoutGlobalScope('active')
                ->whereDate('valid_from', '>', $fechaObjetivo)
                ->orderBy('valid_from', 'asc')
                ->first();
        }

        // NOTA: Se eliminó el "Fallback 3: Activo" ciego. 
        // Si no hay nada cerca del año fiscal, es mejor que falle la validación 
        // a que usemos un valor de 20 años en el futuro o pasado sin querer.
    }

    /**
     * Valida que existan los datos mínimos necesarios para el cálculo.
     * Si falta algo, lanza una excepción que se mostrará al usuario o en logs.
     * 
     * @throws \Exception
     */
    private function validateAvaluoData(): void
    {
        if (!$this->avaluo) {
            // No podemos calcular nada sin un registro de características físicas
            throw new \Exception("No existe información de características físicas (avalúo) válida para el año fiscal {$this->anio}. Por favor, registre las características del predio para esa fecha.");
        }

        $missing = [];

        // Validaciones generales
        if (empty($this->ubigeo) && empty($this->predio->tenant->ubigeo))
            $missing[] = 'Ubigeo (Distrito)';

        // Validaciones según tipo (Urbano/Rústico) para reportar exactamente lo que falta
        if ($this->predio->tipo_predio === 'urbano') {
            if (is_null($this->avaluo->tipo_calzada))
                $missing[] = 'Tipo de Calzada';
            if (is_null($this->avaluo->ancho_via))
                $missing[] = 'Ancho de Vía';
            if (is_null($this->avaluo->tiene_agua))
                $missing[] = 'Servicio de Agua';
            if (is_null($this->avaluo->tiene_desague))
                $missing[] = 'Servicio de Desagüe';
            if (is_null($this->avaluo->tiene_luz))
                $missing[] = 'Servicio de Luz';
        } else {
            // Rústico
            if (is_null($this->avaluo->grupo_tierras))
                $missing[] = 'Grupo de Tierras';
            if (is_null($this->avaluo->calidad_agrologica))
                $missing[] = 'Calidad Agrológica';
            // Distancia a veces es 0, validamos null
            if (is_null($this->avaluo->distancia))
                $missing[] = 'Distancia a Capital';
        }

        if (!empty($missing)) {
            $campos = implode(', ', $missing);
            throw new \Exception("Faltan datos esenciales para el cálculo del año {$this->anio}: [{$campos}]. Revise la ficha del predio.");
        }
    }

    /**
     * Helper para loguear solo en modo local/debug
     */
    protected function logCalculation(string $message): void
    {
        if ($this->logDebug) {
            \Illuminate\Support\Facades\Log::info($message);
        }
    }
}
