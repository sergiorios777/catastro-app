<?php

namespace App\Http\Controllers;

use App\Models\DeterminacionPredial;
use App\Models\PredioFisico;
use App\Services\PdfGeneratorService; // Importar Servicio

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

    public function imprimirPu($predioId)
    {
        $predio = PredioFisico::findOrFail($predioId);
        // Asumimos año actual o lo pasamos por request, aquí uso 2025 como en tu ejemplo
        $anio = request('anio', 2025);

        $pdfContent = $this->pdfService->generatePuContent($predio, $anio);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "inline; filename=PU-{$predio->codigo_referencia}.pdf");
    }
}
