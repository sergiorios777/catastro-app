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
        Schema::create('propietario_predios', function (Blueprint $table) {
            $table->id();
            // 1. Aislamiento Multi-Tenant
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // 2. Las Relaciones
            $table->foreignId('predio_fisico_id')->constrained('predios_fisicos')->cascadeOnDelete();
            $table->foreignId('persona_id')->constrained('personas')->cascadeOnDelete();

            // 3. Datos de la Propiedad
            // Decimal 5,2 permite guardar 100.00 o 33.33
            $table->decimal('porcentaje_propiedad', 5, 2)->default(100.00);

            $table->string('tipo_propiedad')->default('unico'); // unico, copropiedad, sociedad_conyugal, sucesion

            // 4. Vigencia (Historial de propiedad)
            $table->boolean('vigente')->default(true);
            $table->date('fecha_inicio')->nullable(); // Desde cuándo es dueño
            $table->date('fecha_fin')->nullable();    // Hasta cuándo fue dueño

            // Documento que sustenta la propiedad (Escritura, Título, etc.)
            $table->string('documento_sustento')->nullable();

            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index(['tenant_id', 'predio_fisico_id', 'vigente']); // Buscar dueños actuales de un predio
            $table->index(['tenant_id', 'persona_id', 'vigente']);       // Buscar predios actuales de una persona
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('propietario_predios');
    }
};
