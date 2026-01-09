<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReglasDescuentoTributo extends Model
{
    protected $table = 'reglas_descuento_tributos';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo_beneficio',
        'aplicacion_sobre',
        'valor_uit_deducidos',
        'porcentaje_descuento',
        'condiciones_param',
        'base_legal',
        'tipo_tributo',
        'valid_from',
        'valid_to',
        'is_active',
        'tenant_id',
    ];

    protected $casts = [
        'valor_uit_deducidos' => 'decimal:2',
        'porcentaje_descuento' => 'decimal:2',
        'condiciones_param' => 'array',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
    ];
}
