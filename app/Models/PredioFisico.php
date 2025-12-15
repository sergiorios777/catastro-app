<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredioFisico extends Model
{
    use HasFactory, BelongsToTenant;

    // Laravel intenta buscar 'predio_fisicos', definimos la tabla por si acaso
    protected $table = 'predios_fisicos';

    protected $fillable = [
        'tenant_id',
        'cuc',
        'codigo_referencia',
        'direccion',
        'distrito',
        'sector',
        'manzana',
        'lote',
        'latitud',
        'longitud',
        'area_terreno',
        'tipo_predio',
        'zona',
        'estado',
        'es_cuc_provisional',
    ];

    protected $casts = [
        'area_terreno' => 'decimal:4', // Asegura que PHP lo trate como número con decimales
        'es_cuc_provisional' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($predio) {
            // Lógica de CUC Provisional
            if (empty($predio->cuc)) {
                // Caso 1: No ingresaron CUC. Generamos uno basado en la ubicación.
                // Formato sugerido: "PROV-" + Sector + Manzana + Lote
                // Esto ayuda a ubicarlo físicamente.

                $sector = str_pad($predio->sector ?? '00', 2, '0', STR_PAD_LEFT);
                $manzana = str_pad($predio->manzana ?? '000', 3, '0', STR_PAD_LEFT);
                $lote = str_pad($predio->lote ?? '000', 3, '0', STR_PAD_LEFT);

                // Ejemplo: P-01-045-002
                $predio->cuc = "P-{$sector}-{$manzana}-{$lote}";
                $predio->es_cuc_provisional = true;
            } else {
                // Caso 2: Ingresaron un código.
                // Si tiene 12 dígitos numéricos, asumimos que es Oficial (SUNARP).
                // Si no, lo marcamos como provisional/interno.
                if (strlen($predio->cuc) === 12 && ctype_digit($predio->cuc)) {
                    $predio->es_cuc_provisional = false;
                } else {
                    // Es un código manual pero no cumple estándar SUNARP
                    $predio->es_cuc_provisional = true;
                }
            }
        });
    }

    // Relación Muchos a Muchos con Persona
// Usamos 'withPivot' para poder acceder a los campos extra
    public function propietariosActuales()
    {
        return $this->belongsToMany(Persona::class, 'propietario_predios')
            ->using(PropietarioPredio::class)
            ->withPivot(['porcentaje_propiedad', 'tipo_propiedad', 'fecha_inicio', 'vigente'])
            ->withTimestamps()
            ->wherePivot('vigente', true); // Solo trae los dueños actuales
    }

    /**
     * NUEVA FUNCIÓN: Trae TODO el historial (Vigentes + Históricos).
     * Usaremos esta para el Panel Administrativo.
     */
    public function propietarios()
    {
        return $this->belongsToMany(Persona::class, 'propietario_predios')
            ->using(PropietarioPredio::class)
            ->withPivot(['porcentaje_propiedad', 'tipo_propiedad', 'fecha_inicio', 'vigente', 'documento_sustento'])
            ->withTimestamps();
        // NOTA: Aquí NO ponemos 'wherePivot('vigente', true)'. Queremos ver todo.
    }

    /**
     * Scope para filtrar solo predios activos (útil para listados generales)
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}
