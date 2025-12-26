<?php

namespace App\Services;

use App\Models\PredioFisico;
use App\Models\Construccion;
use App\Models\ValorUnitarioEdificacion;
use App\Models\ArancelUrbano;
use App\Models\ArancelRustico;
use App\Models\AnioFiscal;

class CalculoAutoavaluoService
{
    protected PredioFisico $predio;
    protected int $anio;
    protected int $anioId;
    protected string $zona;   // 'costa', 'sierra', 'selva'
    protected string $ubigeo; // 6 dígitos

    public function __construct(PredioFisico $predio, int $anio = null)
    {
        $this->predio = $predio;
        $this->anio = $anio ?? now()->year;

        // Cargar relación Tenant si falta
        if (!$predio->relationLoaded('tenant')) {
            $predio->load('tenant');
        }

        // Datos del Municipio (Tenant)
        $this->zona = $predio->tenant->zona_geografica ?? 'selva';
        $this->ubigeo = $predio->tenant->ubigeo ?? '000000';

        // Obtener el ID del Año Fiscal una sola vez para optimizar
        $this->anioId = AnioFiscal::where('anio', $this->anio)->value('id') ?? 0;
    }

    public function calcularTotal(): array
    {
        // Validamos que exista el año fiscal configurado
        if ($this->anioId === 0) {
            return $this->retornarCeros();
        }

        $valorTerreno = $this->calcularTerreno();
        $valorConstruccion = $this->calcularConstrucciones();
        $valorObras = $this->calcularObrasComplementarias();

        $total = $valorTerreno + $valorConstruccion + $valorObras;

        return [
            'valor_terreno' => round($valorTerreno, 2),
            'valor_construccion' => round($valorConstruccion, 2),
            'valor_obras' => round($valorObras, 2),
            'total_autoavaluo' => round($total, 2),
        ];
    }

    protected function calcularTerreno(): float
    {
        $area = $this->predio->area_terreno;

        $valorArancel = 0;

        if ($this->predio->tipo_predio === 'urbano') {
            // Lógica Urbana EXACTA
            $valorArancel = ArancelUrbano::where('anio_fiscal_id', $this->anioId)
                ->where('ubigeo_distrito', $this->ubigeo)
                // Aquí usamos los campos que el predio tiene en memoria
                ->where('tipo_calzada', $this->predio->tipo_calzada)
                ->where('ancho_via', $this->predio->ancho_via)
                ->where('tiene_agua', $this->predio->tiene_agua)
                ->where('tiene_desague', $this->predio->tiene_desague)
                ->where('tiene_luz', $this->predio->tiene_luz)
                ->value('valor_arancel'); // 'value' trae el campo directo, o null
        } else {
            // Lógica Rústica EXACTA
            $ubigeoProv = substr($this->ubigeo, 0, 4);

            $valorArancel = ArancelRustico::where('anio_fiscal_id', $this->anioId)
                ->where('ubigeo_provincia', $ubigeoProv)
                ->where('grupo_tierras', $this->predio->grupo_tierras)
                ->where('distancia', $this->predio->distancia)
                ->where('calidad_agrologica', $this->predio->calidad_agrologica)
                ->value('valor_arancel');
        }

        // Si no encuentra arancel (ej: configuración faltante), devolvemos 0 o podrías lanzar error.
        return $area * ($valorArancel ?? 0);
    }

    protected function calcularConstrucciones(): float
    {
        $totalConstruccion = 0;

        foreach ($this->predio->construcciones as $piso) { // 

            // 1. Sumatoria de Valores Unitarios (Muros + Techos...)
            $valorUnitarioM2 = $this->obtenerValorUnitarioTotal($piso);

            // 2. Factor de Depreciación
            // Usamos el campo manual que creamos (o el calculado si prefieres lógica estricta)
            // Si es null, asumimos 0 depreciación (100% valor)
            $porcentajeDepr = $piso->porcentaje_depreciacion_manual ?? 0; // [cite: 54]
            $factorDepreciacion = (100 - $porcentajeDepr) / 100;

            // 3. Fórmula: Area * Valor * (1 - Depreciación)
            $valorPiso = $piso->area_construida * $valorUnitarioM2 * $factorDepreciacion; // 

            $totalConstruccion += $valorPiso;
        }

        return $totalConstruccion;
    }

    protected function calcularObrasComplementarias(): float
    {
        $total = 0;

        // Iteramos sobre la relación (pivot) predio_obras_complementarias [cite: 38]
        foreach ($this->predio->obrasComplementarias as $obra) {
            $cantidad = $obra->pivot->cantidad;
            $valorUnitario = $obra->valor ?? 0; // El valor viene del catálogo/maestro vinculado

            // NOTA: Las obras complementarias también se deprecian.
            // Por simplicidad inicial, calculamos valor directo. 
            // Para depreciar obras, necesitaríamos una tabla de depreciación específica para instalaciones.

            $total += ($cantidad * $valorUnitario);
        }

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
}
