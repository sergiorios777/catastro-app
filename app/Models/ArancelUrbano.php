<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArancelUrbano extends Model
{
    protected $fillable = [
        'anio_fiscal_id',
        'ubigeo_distrito', // Clave: 6 dígitos (Ej: 160101)

        // Matriz
        'tipo_calzada',    // tierra, afirmado, empedrado, asfalto, concreto
        'ancho_via',       // menos_6, entre_6_8, mas_8
        'tiene_agua',
        'tiene_desague',
        'tiene_luz',

        'valor_arancel',
    ];

    protected $casts = [
        'tiene_agua' => 'boolean',
        'tiene_desague' => 'boolean',
        'tiene_luz' => 'boolean',
        'valor_arancel' => 'decimal:2',
    ];

    public function anioFiscal(): BelongsTo
    {
        return $this->belongsTo(AnioFiscal::class);
    }

    /**
     * Función Helper para encontrar el precio exacto.
     * Uso: ArancelUrbano::buscar(1, '160101', 'asfalto', 'mas_8', true, true, true);
     */
    public static function buscar($anioId, $ubigeo, $calzada, $ancho, $agua, $desague, $luz)
    {
        return self::where('anio_fiscal_id', $anioId)
            ->where('ubigeo_distrito', $ubigeo)
            ->where('tipo_calzada', $calzada)
            ->where('ancho_via', $ancho)
            ->where('tiene_agua', $agua)
            ->where('tiene_desague', $desague)
            ->where('tiene_luz', $luz)
            ->value('valor_arancel'); // Retorna el precio o null
    }
}
