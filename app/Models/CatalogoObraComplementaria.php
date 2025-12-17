<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoObraComplementaria extends Model
{
    protected $fillable = ['codigo', 'descripcion', 'unidad_medida'];

    public function predioFisicos()
    {
        return $this->belongsToMany(
            PredioFisico::class,
            'predio_obras_complementarias', // La misma tabla pivote
            'catalogo_obra_complementaria_id', // Mi ID en la pivote
            'predio_fisico_id' // El ID del otro en la pivote
        )
            ->withPivot(['cantidad', 'anio_construccion', 'estado_conservacion'])
            ->withTimestamps();
    }
}
