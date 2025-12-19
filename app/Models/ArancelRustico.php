<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArancelRustico extends Model
{
    protected $fillable = [
        'anio_fiscal_id',
        'ubigeo_provincia', // Clave: 4 dígitos (Ej: 1601)

        'grupo_tierras',    // A, C, P, X
        'distancia',        // hasta_1km, de_1_2km... (Nullable)
        'calidad_agrologica', // alta, media, baja (Nullable)

        'valor_arancel',
    ];

    protected $casts = [
        'valor_arancel' => 'decimal:4', // Rústico suele tener más precisión
    ];

    public function anioFiscal(): BelongsTo
    {
        return $this->belongsTo(AnioFiscal::class);
    }

    /**
     * Lógica inteligente para encontrar precios rústicos
     * Maneja automáticamente los NULLs según el grupo de tierra.
     */
    public static function buscar($anioId, $ubigeoProvincia, $grupo, $distancia = null, $calidad = null)
    {
        $query = self::where('anio_fiscal_id', $anioId)
            ->where('ubigeo_provincia', $ubigeoProvincia)
            ->where('grupo_tierras', $grupo);

        // Regla 1: Tierras Eriazas (X) -> No importa distancia ni calidad
        if ($grupo === 'X') {
            return $query->value('valor_arancel');
        }

        // Regla 2: Pastos (P) -> Importa calidad, NO importa distancia
        if ($grupo === 'P') {
            return $query->where('calidad_agrologica', $calidad)
                ->value('valor_arancel');
        }

        // Regla 3: Cultivos (A y C) -> Importa todo
        // (Aptas para Cultivo en Limpio / Permanente)
        return $query->where('distancia', $distancia)
            ->where('calidad_agrologica', $calidad)
            ->value('valor_arancel');
    }
}
