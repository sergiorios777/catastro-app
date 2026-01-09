<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BeneficioPersona extends Model
{
    protected $table = 'beneficio_personas';

    protected $fillable = [
        'persona_id',
        'regla_descuento_tributo_id',
        'tenant_id',
        'documento_resolucion',
        'observacion',
        'valid_from',
        'valid_to',
        'is_active',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function reglaDescuentoTributo()
    {
        return $this->belongsTo(ReglasDescuentoTributo::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Filtra solo los beneficios activos y vigentes a la fecha actual (o dada)
     */
    public function scopeVigentes($query, $fecha = null)
    {
        $fecha = $fecha ?? now();

        return $query->where('is_active', true)
            ->where('valid_from', '<=', $fecha)
            ->where(function ($q) use ($fecha) {
                $q->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $fecha);
            });
    }
}
