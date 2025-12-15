<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable implements HasTenants, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'is_global_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_global_admin' => 'boolean',
        ];
    }

    // 3. DEFINIR LA RELACIÓN CON TENANT
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // 4. MÉTODO OBLIGATORIO: getTenants
    // Filament usa esto para listar a qué tenants puede entrar el usuario.
    public function getTenants(Panel $panel): Collection
    {
        // En nuestro caso, un usuario pertenece a UN solo tenant.
        // Devolvemos una colección que contiene solo su tenant asignado.
        if ($this->tenant) {
            return collect([$this->tenant]);
        }

        return collect();
    }

    // 5. MÉTODO OBLIGATORIO: canAccessTenant
    // Filament verifica aquí si el usuario tiene permiso para entrar al tenant actual.
    public function canAccessTenant(Model $tenant): bool
    {
        // El usuario puede entrar si su tenant_id coincide con el del tenant actual
        return $this->tenant_id === $tenant->id;
    }

    // 6. NUEVO MÉTODO: Lógica de Seguridad Central
    public function canAccessPanel(Panel $panel): bool
    {
        // A. Reglas para el Panel SUPER ADMIN
        if ($panel->getId() === 'admin') {
            // Solo entra si tiene la bandera is_global_admin en TRUE
            return $this->is_global_admin;
        }

        // B. Reglas para el Panel MUNICIPAL (APP)
        if ($panel->getId() === 'app') {
            // Solo entra si tiene un tenant asignado Y es activo
            return $this->tenant_id !== null;
        }

        // C. Por defecto, bloquear cualquier otro panel
        return false;
    }
}
