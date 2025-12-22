<?php

namespace App\Services;

use App\Models\Caja;
use App\Models\Pago;
use App\Models\DeterminacionPredial;
use Illuminate\Support\Facades\DB;
use Exception;

class TesoreriaService
{
    /**
     * Intenta procesar un pago.
     * 1. Verifica si hay caja abierta.
     * 2. Registra el pago.
     * 3. Actualiza la caja.
     * 4. Actualiza la deuda.
     */
    public function procesarPago(DeterminacionPredial $deuda, array $datosPago)
    {
        $user = auth()->user();

        // 1. Validar Caja Abierta
        $caja = Caja::abierta($user->id)->first();

        if (!$caja) {
            throw new Exception("No tienes una caja abierta. Por favor, abre caja antes de cobrar.");
        }

        if ($deuda->estado === 'pagado') {
            throw new Exception("Esta deuda ya fue pagada anteriormente.");
        }

        return DB::transaction(function () use ($caja, $deuda, $datosPago, $user) {

            // 2. Generar Correlativo (Simplificado para el ejemplo)
            // Idealmente esto se saca de una tabla de correlativos por serie
            $ultimoNumero = Pago::where('tenant_id', $user->tenant_id)->max('numero') ?? 0;
            $nuevoNumero = str_pad($ultimoNumero + 1, 8, '0', STR_PAD_LEFT);

            // 3. Crear el Pago
            $pago = Pago::create([
                'tenant_id' => $user->tenant_id,
                'caja_id' => $caja->id,
                'determinacion_predial_id' => $deuda->id,
                'serie' => 'E001', // Serie por defecto
                'numero' => $nuevoNumero,
                'monto_total' => $deuda->impuesto_calculado, // Asumimos pago total por ahora
                'metodo_pago' => $datosPago['metodo_pago'] ?? 'efectivo',
                'referencia_pago' => $datosPago['referencia_pago'] ?? null,
                'fecha_pago' => now(),
                'procesado_por' => $user->id,
            ]);

            // 4. Actualizar Acumulado en Caja
            $caja->increment('total_recaudado', (float) $pago->monto_total);

            // 5. Matar la Deuda
            $deuda->update([
                'estado' => 'pagado',
                // Aquí podrías agregar campos de auditoría si quisieras
            ]);

            return $pago;
        });
    }
}
