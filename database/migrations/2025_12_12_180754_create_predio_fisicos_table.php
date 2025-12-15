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
        Schema::create('predios_fisicos', function (Blueprint $table) {
            $table->id();

            // 1. Aislamiento Multi-Tenant
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // 2. Identificación Catastral
            // El CUC en Perú tiene 12 dígitos, pero lo dejamos string por flexibilidad
            $table->string('cuc', 20)->nullable();

            // Códigos de referencia local (para facilitar ubicación)
            $table->string('codigo_referencia')->nullable()->comment('Código interno anterior o de rentas');

            // 3. Ubicación Física
            $table->string('direccion')->nullable();
            $table->string('distrito')->nullable(); // Ej: Iquitos
            $table->string('sector')->nullable();
            $table->string('manzana', 10)->nullable();
            $table->string('lote', 10)->nullable();

            // Coordenadas referenciales (Opcional, preparación para Etapa 2 GIS)
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();

            // 4. Características Físicas
            // Usamos decimal con alta precisión para el área
            $table->decimal('area_terreno', 12, 4)->default(0); // Hasta 99,999,999.9999

            $table->string('tipo_predio')->default('urbano'); // urbano, rustico
            $table->string('zona')->nullable(); // residencial, comercial, etc.

            // 5. Estado y Control (Vital para el linaje)
            // activo: Predio vigente
            // historico: Predio que fue dividido o fusionado (ya no existe físicamente igual)
            $table->string('estado')->default('activo');

            $table->timestamps();

            // --- ÍNDICES Y RESTRICCIONES ---

            // El CUC debe ser único POR MUNICIPIO.
            // Esto permite que el CUC '123' exista en el Tenant A y en el Tenant B sin conflicto.
            $table->unique(['tenant_id', 'cuc']);

            // Índice para búsquedas rápidas por ubicación
            $table->index(['tenant_id', 'manzana', 'lote']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predio_fisicos');
    }
};
