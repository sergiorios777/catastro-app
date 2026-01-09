<?php

namespace App\Http\Controllers;

use App\Models\DeterminacionPredial;
use App\Models\PredioFisico;
use App\Services\PdfGeneratorService; // Importar Servicio
use Carbon\Carbon;

class DeclaracionJuradaController extends Controller
{
    protected $pdfService;

    public function __construct(PdfGeneratorService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function imprimirHr($determinacionId)
    {
        $determinacion = DeterminacionPredial::with(['persona', 'anioFiscal', 'tenant'])
            ->findOrFail($determinacionId);

        // Usamos el servicio -> output directo al navegador
        // Pasamos el usuario actual explícitamente
        $userName = auth()->user()->name ?? 'Sistema';
        $pdfContent = $this->pdfService->generateHrContent($determinacion, $userName);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "inline; filename=HR-{$determinacion->anioFiscal->anio}.pdf");
    }

    public function imprimirPu($predioId, $anio = null)
    {
        $predio = PredioFisico::findOrFail($predioId);

        $pdfContent = $this->pdfService->generatePuContent($predio, $anio ?? Carbon::now()->year);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "inline; filename=PU-{$predio->cuc}-{$anio}.pdf");
    }
}
