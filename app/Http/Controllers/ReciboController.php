<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReciboController extends Controller
{
    // Método existente (A4/Carta)
    public function imprimir($pagoId)
    {
        $pago = $this->obtenerDatosPago($pagoId);

        $pdf = Pdf::loadView('pdfs.recibo', [
            'pago' => $pago,
            'municipio' => $pago->tenant,
            'contribuyente' => $pago->determinacion->persona,
        ]);

        return $pdf->stream("Recibo-{$pago->serie}-{$pago->numero}.pdf");
    }

    // NUEVO: Método para Ticket 80mm
    public function imprimirTicket($pagoId)
    {
        $pago = $this->obtenerDatosPago($pagoId);

        $pdf = Pdf::loadView('pdfs.ticket-80mm', [
            'pago' => $pago,
            'municipio' => $pago->tenant,
            'contribuyente' => $pago->determinacion->persona,
        ]);

        // Configuración clave para 80mm
        // [0, 0, ancho, largo] -> 226.77 pt son aprox 80mm
        // El largo (800) es un estimado, la impresora térmica suele cortar donde termina el texto
        // o puedes hacerlo más largo si el ticket es extenso.
        $pdf->setPaper([0, 0, 226.77, 800], 'portrait');

        return $pdf->stream("Ticket-{$pago->serie}-{$pago->numero}.pdf");
    }

    // Helper privado para no repetir código
    private function obtenerDatosPago($pagoId)
    {
        return Pago::with(['determinacion.persona', 'determinacion.anioFiscal', 'procesador', 'tenant'])
            ->findOrFail($pagoId);
    }
}
