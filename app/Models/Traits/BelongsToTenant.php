<?php

namespace App\Models\Traits;

use App\Models\Tenant;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    /**
     * El "boot" del trait se ejecuta automáticamente cuando el modelo se usa.
     */
    protected static function bootBelongsToTenant(): void
    {
        // 1. GLOBAL SCOPE: Filtra las consultas (SELECT)
        /*static::addGlobalScope('tenant', function (Builder $builder) {
            // Solo aplicamos el filtro si Filament ha detectado un Tenant activo.
            // Esto permite que el Super Admin (que no tiene tenant activo) vea todo.
            if (Filament::getTenant()) {
                $builder->where('tenant_id', Filament::getTenant()->id);
            }
        });*/

        // 1. GLOBAL SCOPE: Filtra las consultas (SELECT)
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Filament::getTenant()) {
                // CORRECCIÓN AQUÍ:
                // Usamos qualifyColumn() para que Laravel ponga "tabla.tenant_id"
                // en lugar de solo "tenant_id".
                $builder->where(
                    $builder->getModel()->qualifyColumn('tenant_id'),
                    Filament::getTenant()->id
                );
            }
        });

        // 2. CREATING EVENT: Asigna el ID antes de guardar (INSERT)
        static::creating(function (Model $model) {
            // Si el modelo no tiene tenant_id asignado y hay un tenant activo...
            if (!$model->tenant_id && Filament::getTenant()) {
                $model->tenant_id = Filament::getTenant()->id;
            }
        });
    }

    /**
     * Define la relación estándar con el modelo Tenant.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
