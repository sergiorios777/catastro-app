<?php

namespace App\Services;

use App\Models\Persona;
use App\Models\AnioFiscal;
use App\Models\DeterminacionPredial;
use App\Models\PropietarioPredio;

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
     */
    public function generarDeterminacion(): DeterminacionPredial
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

            // Instanciamos el servicio que creamos ayer para cada predio
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
}
