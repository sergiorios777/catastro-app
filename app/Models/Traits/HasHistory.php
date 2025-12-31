<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

trait HasHistory
{
    public static function bootHasHistory()
    {
        static::creating(function ($model) {
            // 1. Asignar Track ID si no existe
            if (empty($model->track_id)) {
                $model->track_id = (string) Str::uuid();
            }

            // 2. CORRECCIÓN: Solo asignar versión 1 si el modelo NO tiene versión
            if (empty($model->version)) {
                $model->version = 1;
            }

            // 3. is_active siempre debe ser true al nacer (sea versión 1 o 20)
            // Usamos $model->is_active ?? true para respetar si alguien lo mandó como false explícitamente (raro, pero posible)
            if (!isset($model->is_active)) {
                $model->is_active = true;
            }

            // 4. Fecha de vigencia
            if (empty($model->valid_from)) {
                $model->valid_from = now();
            }
        });

        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('is_active', true);
        });
    }

    /**
     * Crea una nueva versión histórica del registro actual.
     * @param array $newAttributes Los datos que cambian
     */
    public function createNewVersion(array $newAttributes): self
    {
        return DB::transaction(function () use ($newAttributes) {
            // A. "Cerramos" la vigencia del registro actual
            // Usamos updateQuietly para evitar disparar eventos innecesarios
            $this->updateQuietly([
                'is_active' => false,
                'valid_to' => now(),
            ]);

            // B. Clonamos el registro actual para crear el nuevo
            // 'replicate' copia todo excepto el ID
            $newVersion = $this->replicate(['id', 'created_at', 'updated_at']);

            // C. Aplicamos los cambios nuevos
            $newVersion->fill($newAttributes);

            // D. Configuramos la metadata de la nueva versión
            $newVersion->version = $this->version + 1;
            $newVersion->is_active = true;
            $newVersion->valid_from = now();
            $newVersion->valid_to = null;
            $newVersion->track_id = $this->track_id; // Mantiene el mismo hilo histórico

            $newVersion->save();

            return $newVersion;
        });
    }

    // Método auxiliar para ver el historial (ignorando el Scope 'active')
    public function history()
    {
        return static::withoutGlobalScope('active')
            ->where('track_id', $this->track_id)
            ->orderBy('version', 'desc');
    }
}
