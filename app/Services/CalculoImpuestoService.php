<?php

namespace App\Services;

use App\Models\Persona;
use App\Models\AnioFiscal;
use App\Models\DeterminacionPredial;
use App\Models\PropietarioPredio;
use App\Models\ArancelUrbano;
use App\Models\ArancelRustico;

class CalculoImpuestoService
{
    protected Persona $persona;
    protected int $anio;
    protected AnioFiscal $anioFiscal;

    public function __construct(Persona $persona, int $anio)
    {
        $this->persona = $persona;
        $this->anio = $anio;

        // Buscamos el año fiscal (y su UIT)
        $this->anioFiscal = AnioFiscal::where('anio', $anio)->firstOrFail();
    }

    /**
     * Calcula y guarda/actualiza la determinación del impuesto
     * @param array $sobrescrituras Array opcional [predio_id => ['tipo_calzada' => 'tierra', ...]]
     */
    public function generarDeterminacion(array $sobrescrituras = []): DeterminacionPredial
    {
        // 1. Obtener todos los predios del contribuyente en este Tenant
        // Filtramos solo los que están vigentes como propietarios
        $relaciones = PropietarioPredio::with('predioFisico')
            ->where('persona_id', $this->persona->id)
            ->where('tenant_id', $this->persona->tenant_id)
            ->where('vigente', true)
            ->get();

        $sumaAutoavaluos = 0;
        $conteoPredios = 0;

        // 2. Calcular Autoavalúo individual de cada predio y sumar
        foreach ($relaciones as $relacion) {
            $predio = $relacion->predioFisico;

            // --- LÓGICA DE SIMULACIÓN / HISTÓRICO ---
            // Si hay datos simulados para este predio, los "inyectamos" temporalmente
            if (isset($sobrescrituras[$predio->id])) {
                $predio->fill($sobrescrituras[$predio->id]);
                // OJO: fill() solo cambia en memoria, NO guarda en BD. ¡Exactamente lo que queremos!
            }

            // Instanciamos el servicio que creamos para cada predio
            $servicioPredio = new CalculoAutoavaluoService($predio, $this->anio);
            $totales = $servicioPredio->calcularTotal();

            // Si tiene % de propiedad (ej: condominios), aplicamos el %
            $valorParte = $totales['total_autoavaluo'] * ($relacion->porcentaje_propiedad / 100);

            $sumaAutoavaluos += $valorParte;
            $conteoPredios++;
        }

        // 3. Aplicar Escala del Impuesto Predial (Lógica UIT)
        $impuestoTotal = $this->calcularImpuestoEscalonado($sumaAutoavaluos, (float) $this->anioFiscal->valor_uit);

        // 4. Validar Impuesto Mínimo (0.6% de la UIT)
        // Según norma, el impuesto no puede ser menor a esto.
        $impuestoMinimo = $this->anioFiscal->tasa_minima_predial ?? ($this->anioFiscal->valor_uit * 0.006);

        if ($impuestoTotal < $impuestoMinimo) {
            $impuestoTotal = $impuestoMinimo;
        }

        // 5. Cargar una estructura profunda para guardar en el historial
        // Queremos guardar: Predio + Construcciones + Obras + Info del Propietario en ese momento
        $datosParaSnapshot = PropietarioPredio::with([
            'predioFisico.construcciones',        // Pisos y materiales
            'predioFisico.obrasComplementarias',  // Piscinas, muros
            'predioFisico.tenant',                // Ubicación administrativa
        ])
            ->where('persona_id', $this->persona->id)
            ->where('tenant_id', $this->persona->tenant_id)
            ->where('vigente', true)
            ->get()
            ->toArray(); // Convertimos toda la colección de objetos a un Array puro

        // 6. Guardar en Base de Datos (Upsert)
        return DeterminacionPredial::updateOrCreate(
            [
                'tenant_id' => $this->persona->tenant_id,
                'persona_id' => $this->persona->id,
                'anio_fiscal_id' => $this->anioFiscal->id,
            ],
            [
                'cantidad_predios' => $conteoPredios,
                'base_imponible' => $sumaAutoavaluos,
                'valor_uit' => $this->anioFiscal->valor_uit,
                'impuesto_calculado' => $impuestoTotal,
                'tasa_minima' => $impuestoMinimo,
                'fecha_emision' => now(),
                'snapshot_datos' => $datosParaSnapshot,
            ]
        );
    }

    /**
     * La Lógica Pura de Tramos (Perú)
     */
    protected function calcularImpuestoEscalonado(float $baseImponible, float $uit): float
    {
        $impuesto = 0;
        $montoRestante = $baseImponible;

        // TRAMO 1: Hasta 15 UIT (0.2%)
        $tramo1 = 15 * $uit;

        if ($montoRestante > $tramo1) {
            $impuesto += ($tramo1 * 0.002);
            $montoRestante -= $tramo1;
        } else {
            $impuesto += ($montoRestante * 0.002);
            return $impuesto; // Se acabó aquí
        }

        // TRAMO 2: Exceso de 15 hasta 60 UIT (0.6%)
        // (La diferencia son 45 UIT)
        $tramo2 = 45 * $uit;

        if ($montoRestante > $tramo2) {
            $impuesto += ($tramo2 * 0.006);
            $montoRestante -= $tramo2;
        } else {
            $impuesto += ($montoRestante * 0.006);
            return $impuesto; // Se acabó aquí
        }

        // TRAMO 3: Exceso de 60 UIT (1.0%)
        // Lo que queda
        $impuesto += ($montoRestante * 0.010);

        return $impuesto;
    }

    /**
     * Función auxiliar para buscar el precio en las tablas de aranceles
     */
    private function obtenerArancelM2($predio): float
    {
        if ($predio->tipo_predio === 'urbano') {
            // Buscamos en Arancel Urbano
            $arancel = ArancelUrbano::where('anio_fiscal_id', $this->anioFiscal->id)
                ->where('ubigeo_distrito', $predio->tenant->ubigeo ?? '000000') // Asumiendo relación tenant
                ->where('tipo_calzada', $predio->tipo_calzada)
                ->where('ancho_via', $predio->ancho_via)
                ->where('tiene_agua', $predio->tiene_agua)
                ->where('tiene_desague', $predio->tiene_desague)
                ->where('tiene_luz', $predio->tiene_luz)
                ->first();

            return $arancel ? (float) $arancel->valor_arancel : 0.0;
        }

        if ($predio->tipo_predio === 'rustico') {
            // Buscamos en Arancel Rústico
            // Nota: Ajusta 'ubigeo_provincia' según tu lógica real de ubicación
            $arancel = ArancelRustico::where('anio_fiscal_id', $this->anioFiscal->id)
                ->where('grupo_tierras', $predio->grupo_tierras)
                ->where('distancia', $predio->distancia)
                ->where('calidad_agrologica', $predio->calidad_agrologica)
                ->first();

            return $arancel ? (float) $arancel->valor_arancel : 0.0;
        }

        return 0.0;
    }
}
