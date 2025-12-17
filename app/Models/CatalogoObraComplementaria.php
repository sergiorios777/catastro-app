<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoObraComplementaria extends Model
{
    protected $fillable = ['codigo', 'descripcion', 'unidad_medida'];
}
