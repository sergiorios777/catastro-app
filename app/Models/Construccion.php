<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Construccion extends Model
{
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
    ];

    public function predioFisico(): BelongsTo
    {
        return $this->belongsTo(PredioFisico::class);
    }
}
