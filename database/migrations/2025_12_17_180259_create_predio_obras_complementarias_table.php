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
        Schema::create('predio_obras_complementarias', function (Blueprint $table) {
            $table->id();

            // 1. Relaciones
            $table->foreignId('predio_fisico_id')
                ->constrained('predios_fisicos')
                ->cascadeOnDelete();

            $table->foreignId('catalogo_obra_complementaria_id')
                ->constrained('catalogo_obra_complementarias')
                ->cascadeOnDelete()
                ->name('fk_predio_obra_cat_id'); // Nombre corto

            // 2. Características Específicas
            $table->decimal('cantidad', 10, 2); // Ej: 20.00 (m2, ml, unid)

            // 3. Depreciación (Igual que en Construcción)
            $table->integer('anio_construccion');
            $table->enum('estado_conservacion', ['muy_bueno', 'bueno', 'regular', 'malo'])
                ->default('regular');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predio_obras_complementarias');
    }
};
