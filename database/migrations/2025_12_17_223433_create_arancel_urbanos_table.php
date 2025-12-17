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
        Schema::create('arancel_urbanos', function (Blueprint $table) {
            $table->id();

            // 1. Ubicación Temporal y Espacial
            $table->foreignId('anio_fiscal_id')->constrained()->cascadeOnDelete();
            $table->string('ubigeo_distrito', 6)->index(); // Ej: '160101'

            // 2. Matriz de Características (Indices)
            $table->enum('tipo_calzada', ['tierra', 'afirmado', 'empedrado', 'asfalto', 'concreto']);
            $table->enum('ancho_via', ['menos_6', 'entre_6_8', 'mas_8']); // Simplificamos los keys

            // Servicios (Booleanos para optimizar la matriz)
            $table->boolean('tiene_agua');
            $table->boolean('tiene_desague');
            $table->boolean('tiene_luz');

            // 3. El Valor
            $table->decimal('valor_arancel', 10, 2); // Precio por m2

            $table->timestamps();

            // Clave Única Compuesta:
            // En un distrito, año y combinación exacta de servicios, solo hay UN precio.
            $table->unique([
                'anio_fiscal_id',
                'ubigeo_distrito',
                'tipo_calzada',
                'ancho_via',
                'tiene_agua',
                'tiene_desague',
                'tiene_luz'
            ], 'unique_arancel_urbano_matrix');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arancel_urbanos');
    }
};
