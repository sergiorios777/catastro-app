<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\HasHistory;

class Construccion extends Model
{
    use HasHistory;

    protected $fillable = [
        'predio_fisico_id',
        'nro_piso',
        'seccion',
        'area_construida',
        'muros_columnas',
        'techos',
        'pisos',
        'puertas_ventanas',
        'revestimientos',
        'banos',
        'inst_electricas_sanitarias',
        'anio_construccion',
        'estado_conservacion',
        'material_estructural',
        'uso_especifico',
        'porcentaje_depreciacion_calculado',
        'porcentaje_depreciacion_manual',
        'track_id',
        'version',
        'is_active',
        'valid_from',
        'valid_to',
    ];

    public function predioFisico(): BelongsTo
    {
        return $this->belongsTo(PredioFisico::class);
    }
}
