<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    public function imprimirArqueo($cajaId)
    {
        // Cargamos la caja con sus pagos y el usuario (cajero)
        // También traemos la relación con Tenant para el encabezado
        $caja = Caja::with(['pagos.determinacion.persona', 'cajero', 'tenant'])
            ->findOrFail($cajaId);

        // Cálculos matemáticos para el reporte
        $totalSistema = $caja->monto_apertura + $caja->total_recaudado;
        $diferencia = $caja->monto_cierre - $totalSistema; // Positivo = Sobra, Negativo = Falta

        $pdf = Pdf::loadView('pdfs.arqueo', [
            'caja' => $caja,
            'pagos' => $caja->pagos, // Lista de movimientos
            'municipio' => $caja->tenant,
            'resumen' => [
                'total_sistema' => $totalSistema,
                'diferencia' => $diferencia,
                'estado_cuadre' => $diferencia == 0 ? 'CUADRADO' : ($diferencia > 0 ? 'SOBRANTE' : 'FALTANTE')
            ]
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("Arqueo-{$caja->fecha_apertura->format('Ymd')}-{$caja->cajero->name}.pdf");
    }

    // Imprimir cierre de arqueo de caja en ticket de 80 mm
    public function imprimirArqueoTicket($cajaId)
    {
        // Reutilizamos la misma lógica de carga de datos
        $caja = Caja::with(['pagos.determinacion.persona', 'cajero', 'tenant'])
            ->findOrFail($cajaId);

        $totalSistema = $caja->monto_apertura + $caja->total_recaudado;
        $diferencia = $caja->monto_cierre - $totalSistema;

        $pdf = Pdf::loadView('pdfs.arqueo-ticket', [
            'caja' => $caja,
            'municipio' => $caja->tenant,
            'resumen' => [
                'total_sistema' => $totalSistema,
                'diferencia' => $diferencia,
                'estado_cuadre' => $diferencia == 0 ? 'CUADRADO' : ($diferencia > 0 ? 'SOBRANTE' : 'FALTANTE')
            ]
        ]);

        // Configuración para 80mm (ancho 226pt). El largo lo ponemos generoso (1000)
        // para que quepan varios movimientos, la impresora cortará donde termine el texto.
        $pdf->setPaper([0, 0, 226.77, 1000], 'portrait');

        return $pdf->stream("Cierre-Ticket-{$caja->id}.pdf");
    }
}