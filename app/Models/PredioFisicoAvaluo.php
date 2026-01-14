<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\HasHistory;

class PredioFisicoAvaluo extends Model
{
    use HasHistory;

    protected $table = 'predios_fisicos_avaluo';

    protected $fillable = [
        'predio_fisico_id',
        'area_terreno',
        'zona',
        'tipo_predio',
        'tipo_calzada',
        'ancho_via',
        'tiene_agua',
        'tiene_desague',
        'tiene_luz',
        'grupo_tierras',
        'distancia',
        'calidad_agrologica',
        'info_complementaria',
        'info_avaluo',
        'track_id',
        'version',
        'is_active',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'info_complementaria' => 'array',
        'info_avaluo' => 'array',
        'area_terreno' => 'decimal:4', // Asegura que PHP lo trate como número con decimales
        'es_cuc_provisional' => 'boolean',
        'tiene_agua' => 'boolean',
        'tiene_desague' => 'boolean',
        'tiene_luz' => 'boolean',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relaciones:
    // 1. Relación con PredioFisico
    public function predioFisico(): BelongsTo
    {
        return $this->belongsTo(PredioFisico::class);
    }
}
