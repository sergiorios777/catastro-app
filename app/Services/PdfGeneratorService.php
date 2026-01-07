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
        // Lógica recuperada de tu DeclaracionJuradaController
        if (!empty($determinacion->snapshot_datos)) {
            $prediosData = json_decode(json_encode($determinacion->snapshot_datos), false);
            $predios = collect($prediosData)->map(function ($item) {
                if (isset($item->predio_fisico)) {
                    $item->predioFisico = $item->predio_fisico;
                }
                return $item;
            });
        } else {
            $predios = PropietarioPredio::with('predioFisico')
                ->where('persona_id', $determinacion->persona_id)
                ->where('tenant_id', $determinacion->tenant_id)
                ->where('vigente', true)
                ->get();
        }

        $pdf = Pdf::loadView('pdfs.hr', [
            'determinacion' => $determinacion,
            'predios' => $predios,
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
        $predio->loadMissing(['construcciones', 'obrasComplementarias', 'tenant', 'propietarios']);

        $persona = $predio->propietarios
            ->filter(fn($prop) => $prop->pivot->vigente)
            ->first();

        $pdf = Pdf::loadView('pdfs.pu', [
            'predio' => $predio,
            'persona' => $persona,
            'anio' => $anio,
            'municipio' => $predio->tenant
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->output();
    }
}
