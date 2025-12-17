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
        Schema::create('construccions', function (Blueprint $table) {
            $table->id();
            // 1. Pertenencia
            $table->foreignId('predio_fisico_id')->constrained('predios_fisicos')->cascadeOnDelete();

            // 2. Identificación Física
            $table->integer('nro_piso')->default(1); // Piso 1, 2, 3...
            $table->string('seccion', 10)->nullable(); // Bloque A, Bloque B... (Opcional)
            $table->decimal('area_construida', 10, 2); // m2

            // 3. Componentes de Valuación (Las letras A-J)
            // Usamos char(1) porque solo guardamos la letra. Nullable porque puede no tener ese componente.
            $table->char('muros_columnas', 1)->nullable();
            $table->char('techos', 1)->nullable();
            $table->char('pisos', 1)->nullable();
            $table->char('puertas_ventanas', 1)->nullable();
            $table->char('revestimientos', 1)->nullable();
            $table->char('banos', 1)->nullable();
            $table->char('inst_electricas_sanitarias', 1)->nullable();

            // 4. Depreciación
            $table->integer('anio_construccion'); // Para calcular la antigüedad

            $table->enum('estado_conservacion', ['muy_bueno', 'bueno', 'regular', 'malo'])
                ->default('regular');

            $table->enum('material_estructural', ['concreto', 'ladrillo', 'adobe', 'madera', 'drywall'])
                ->default('concreto'); // Esto define qué tabla de depreciación usar

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('construccions');
    }
};
