<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 1. Relación con Tenant (Inquilino)
            // Nullable: porque el SuperAdmin central no pertenece a un tenant específico
            // Constrained: asegura que el ID exista en la tabla 'tenants'
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('id') // Para orden visual en la BD
                ->constrained('tenants')
                ->nullOnDelete();

            // 2. Campo para distinguir si es admin global (del sistema)
            $table->boolean('is_global_admin')
                ->default(false)
                ->after('password');

            // 3. Índice compuesto para optimizar búsquedas dentro de un tenant
            // Esto es vital para el rendimiento cuando tengas muchos usuarios
            $table->index(['tenant_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminamos índices y columnas si revertimos
            $table->dropIndex(['tenant_id', 'email']);
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'is_global_admin']);
        });
    }
};
