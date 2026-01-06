<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use BelongsToTenant;
    protected $guarded = [];

    // Accesores útiles para Filament (opcional pero recomendado)
    public function getNombreCompletoAttribute()
    {
        return $this->tipo_persona === 'juridica'
            ? $this->razon_social
            : "{$this->apellidos}, {$this->nombres}";
    }

    /**
     * Relación inversa: Predios de los que esta persona es propietaria.
     */
    public function predioFisicos()
    {
        return $this->belongsToMany(PredioFisico::class, 'propietario_predios')
            ->using(PropietarioPredio::class) // Usar nuestro modelo Pivote personalizado
            ->withPivot(['porcentaje_propiedad', 'tipo_propiedad', 'fecha_inicio', 'vigente'])
            ->withTimestamps()
            ->wherePivot('vigente', true);
    }

    public function determinaciones()
    {
        return $this->hasMany(DeterminacionPredial::class);
    }
}
