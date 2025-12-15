<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\Pivot; // Usamos Pivot si lo manejamos como tal, o Model estándar.
// Filament v4 maneja mejor relaciones complejas con Models estándar que actúan como pivote.
use Illuminate\Database\Eloquent\Model;

class PropietarioPredio extends Pivot
{
    use BelongsToTenant;

    // Importante: Como definimos $table->id() en la migración, 
    // debemos decirle a Laravel que este pivote sí tiene ID incremental.
    public $incrementing = true;

    protected $table = 'propietario_predios';

    protected $fillable = [
        'tenant_id',
        'predio_fisico_id',
        'persona_id',
        'porcentaje_propiedad',
        'tipo_propiedad',
        'vigente',
        'fecha_inicio',
        'fecha_fin',
        'documento_sustento'
    ];

    protected $casts = [
        'vigente' => 'boolean',
        'porcentaje_propiedad' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    // Relaciones Inversas
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function predioFisico()
    {
        return $this->belongsTo(PredioFisico::class);
    }
}
