<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /* User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]); */

        // 1. Crear el primer Municipio (Tenant)
        // Usamos firstOrCreate para evitar duplicados si corres el seeder varias veces
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo'], // Buscamos por slug
            [
                'name' => 'Municipalidad Distrital de Demo',
                'status' => 'active',
                'subscription_active' => true,
            ]
        );

        // 2. Crear el Usuario Administrador para ese Municipio
        User::firstOrCreate(
            ['email' => 'admin@demo.com'], // Buscamos por email
            [
                'name' => 'Administrador Demo',
                'password' => Hash::make('password'), // La contraseña será "password"
                'tenant_id' => $tenant->id, // ¡Importante! Lo vinculamos al tenant
                'is_global_admin' => false,
            ]
        );

        // 3. (Opcional) Crear un Super Admin Global (para el panel central)
        User::firstOrCreate(
            ['email' => 'superadmin@catastro.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'tenant_id' => null, // No pertenece a ningún tenant
                'is_global_admin' => true,
            ]
        );

        $this->command->info('✅ Datos de prueba creados exitosamente.');
        $this->command->info('   Tenant: demo');
        $this->command->info('   Usuario: admin@demo.com / password');
    }
}
