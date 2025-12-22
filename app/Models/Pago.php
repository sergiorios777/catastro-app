<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $fillable = [
        'tenant_id',
        'caja_id',
        'determinacion_predial_id',
        'serie',
        'numero',
        'monto_total',
        'metodo_pago',
        'referencia_pago',
        'fecha_pago',
        'procesado_por'
    ];

    protected $casts = [
        'fecha_pago' => 'datetime',
        'monto_total' => 'decimal:2',
    ];

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class);
    }

    public function determinacion(): BelongsTo
    {
        return $this->belongsTo(DeterminacionPredial::class, 'determinacion_predial_id');
    }

    public function procesador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'procesado_por');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
