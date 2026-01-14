<?php

namespace App\Services;

use App\Models\DeterminacionPredial;
use App\Models\PredioFisico;
use App\Models\PropietarioPredio;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class PdfGeneratorService
{
    /**
     * Genera el binario del PDF HR
     */
    public function generateHrContent(DeterminacionPredial $determinacion, string $userName = 'Sistema'): string
    {
        // Lógica para obtener los predios (Desde Snapshot o Base de Datos)
        if (!empty($determinacion->snapshot_datos)) {
            // 1. Obtener la data cruda.
            // Como tu snapshot ahora tiene estructura ['predios' => ..., 'calculos' => ...], accedemos a la key 'predios'
            $snapshot = $determinacion->snapshot_datos;

            // Si el snapshot se guardó con la estructura nueva, usamos la key 'predios'.
            // Si es un registro antiguo, podría ser el array directo. Validamos ambos casos.
            $rawPredios = isset($snapshot['predios']) ? $snapshot['predios'] : $snapshot;

            // 2. Convertir Array a Objeto y Mapear nombres
            $predios = collect($rawPredios)->map(function ($item) {
                // Convertimos el item principal de Array a Objeto
                $obj = (object) $item;

                // Corregimos la relación: La vista espera 'predioFisico', pero toArray() guardó 'predio_fisico'
                if (isset($obj->predio_fisico)) {
                    // Convertimos también el predio interno a objeto para poder usar ->codigo_referencia
                    $obj->predioFisico = (object) $obj->predio_fisico;
                } elseif (isset($item['predio_fisico'])) {
                    // Caso redundante de seguridad
                    $obj->predioFisico = (object) $item['predio_fisico'];
                }

                return $obj;
            });

            // Opcional: Extraer cálculos adicionales si los necesitas en la vista
            $calculos = isset($snapshot['calculos_internos']) ? $snapshot['calculos_internos'] : [];
            // $resumen = isset($snapshot['resumen_economico']) ? $snapshot['resumen_economico'] : [];

        } else {
            // Fallback: Si no hay snapshot (registros antiguos), buscamos en vivo en la BD
            $predios = PropietarioPredio::with('predioFisico')
                ->where('persona_id', $determinacion->persona_id)
                ->where('tenant_id', $determinacion->tenant_id)
                ->where('vigente', true)
                ->get();
        }

        $pdf = Pdf::loadView('pdfs.hr', [
            'determinacion' => $determinacion,
            'predios' => $predios,
            'calculos' => $calculos,
            'anio' => $determinacion->anioFiscal->anio,
            'municipio' => $determinacion->tenant,
            'user_name' => $userName
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->output(); // Retorna el contenido crudo del PDF
    }

    /**
     * Genera el binario del PDF PU
     */
    public function generatePuContent(PredioFisico $predio, int $anio): string
    {
        // Cargar relaciones si no están cargadas
        $predio->loadMissing(['construcciones', 'obrasComplementarias', 'tenant', 'propietarios', 'beneficios']);

        $persona = $predio->propietarios
            ->filter(fn($prop) => $prop->pivot->vigente)
            ->first();

        $pdf = Pdf::loadView('pdfs.pu', [
            'predio' => $predio,
            'avaluo' => $predio->avaluoActivo(),
            'persona' => $persona,
            'anio' => $anio,
            'municipio' => $predio->tenant
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->output();
    }
}
