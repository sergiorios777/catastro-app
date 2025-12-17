<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnioFiscal extends Model
{
    protected $fillable = [
        'anio',
        'valor_uit',
        'tasa_ipm',
        'costo_emision',
        'tasa_minima_predial',
        'activo',
        'factor_oficializacion',
    ];

    protected $casts = [
        'valor_uit' => 'decimal:2',
        'tasa_ipm' => 'decimal:2',
        'costo_emision' => 'decimal:2',
        'activo' => 'boolean',
        'factor_oficializacion' => 'decimal:2',
    ];
}
