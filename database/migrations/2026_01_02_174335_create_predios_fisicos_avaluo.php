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
        Schema::create('predios_fisicos_avaluo', function (Blueprint $table) {
            // 2. TABLA ESTADO / AVALÚO (Versionable - SCD Type 2)

            $table->id();
            $table->uuid('track_id')->nullable()->index()->after('id');
            $table->foreignId('predio_fisico_id')->constrained('predios_fisicos')->cascadeOnDelete();

            // Datos Económicos y Físicos Variables
            $table->decimal('area_terreno', 12, 4)->default(0);
            $table->string('zona')->nullable();

            // Características Arancelarias
            $table->enum('tipo_calzada', ['tierra', 'afirmado', 'empedrado', 'asfalto', 'concreto'])->nullable();
            $table->enum('ancho_via', ['menos_6', 'entre_6_8', 'mas_8'])->nullable();
            $table->boolean('tiene_agua')->default(false);
            $table->boolean('tiene_desague')->default(false);
            $table->boolean('tiene_luz')->default(false);

            // Datos Rústicos
            $table->char('grupo_tierras', 1)->nullable()->comment('A: Cultivos en limpio, C: Cultivos permanentes, P: Pastos, X: Eriazas');
            $table->enum('distancia', ['hasta_1km', 'de_1_2km', 'de_2_3km', 'mas_3km'])->nullable();
            $table->enum('calidad_agrologica', ['alta', 'media', 'baja'])->nullable();

            // JSONB para detalles flexibles (Linderos, etc.)
            $table->jsonb('info_complementaria')->nullable();

            // Motor de Versionado
            $table->integer('version')->default(1);
            $table->boolean('is_active')->default(true); // Sin índice único, permitimos múltiples false
            $table->dateTime('valid_from')->useCurrent();
            $table->dateTime('valid_to')->nullable();

            $table->timestamps();

            // Índice para búsquedas rápidas de la versión activa
            $table->index(['predio_id', 'is_active']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predios_fisicos_avaluo');
    }
};
