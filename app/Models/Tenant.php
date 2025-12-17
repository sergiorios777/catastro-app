<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $guarded = [];

    protected $casts = [
        'zona_geografica' => 'string',
    ];

    /**
     * Helper para Arancel Rústico:
     * Extrae los primeros 4 dígitos del ubigeo (La Provincia).
     * Ej: Si el distrito es 160506 -> Retorna '1605'
     */
    public function getUbigeoProvinciaAttribute(): ?string
    {
        if (!$this->ubigeo || strlen($this->ubigeo) < 4) {
            return null;
        }
        return substr($this->ubigeo, 0, 4);
    }
}
