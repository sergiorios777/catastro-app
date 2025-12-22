<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Caja extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'fecha_apertura',
        'fecha_cierre',
        'monto_apertura',
        'monto_cierre',
        'total_recaudado',
        'estado',
        'observaciones'
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime', // <--- Esto soluciona el error
        'fecha_cierre' => 'datetime',   // <--- Esto previene el error en el cierre
        'monto_apertura' => 'decimal:2',
        'total_recaudado' => 'decimal:2',
        'monto_cierre' => 'decimal:2',
    ];

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function cajero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scope para buscar caja abierta del usuario actual
    public function scopeAbierta($query, $userId)
    {
        return $query->where('user_id', $userId)->where('estado', 'abierta');
    }
}
