<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class DeterminacionPredial extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        "tenant_id",
        "persona_id",
        "anio_fiscal_id",
        "cantidad_predios",
        "base_imponible",
        "valor_uit",
        "impuesto_calculado",
        "tasa_minima",
        "estado",
        "fecha_emision",
        "snapshot_datos",
    ];

    protected $casts = [
        'snapshot_datos' => 'array', // <--- MAGIA: Convierte JSON <-> Array automáticamente
        'base_imponible' => 'decimal:2',
        'impuesto_calculado' => 'decimal:2',
        'tasa_minima' => 'decimal:2',
        'valor_uit' => 'decimal:2',
    ];

    /* Relaciones */

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function anioFiscal()
    {
        return $this->belongsTo(AnioFiscal::class);
    }
}
