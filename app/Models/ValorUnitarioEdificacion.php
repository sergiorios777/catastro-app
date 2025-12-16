<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValorUnitarioEdificacion extends Model
{
    protected $fillable = [
        'anio_fiscal_id',
        'zona_geografica',
        'componente',
        'categoria',
        'valor',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
    ];

    public function anioFiscal(): BelongsTo
    {
        return $this->belongsTo(AnioFiscal::class);
    }
}
