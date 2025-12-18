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
        Schema::create('depreciacions', function (Blueprint $table) {
            $table->id();
            // 1. Material (Factor Estructural)
            // Agrupamos según RNT: Concreto/Ladrillo vs Adobe/Madera
            $table->enum('material', ['concreto', 'ladrillo', 'adobe', 'madera'])->index();

            // 2. Uso (Factor de Clasificación RNT - Tablas 1, 2, 3, 4)
            $table->enum('uso', [
                'casa_habitacion',      // 1.1 (Tabla 1)
                'tienda_deposito',      // 1.2 (Tabla 2)
                'edificio_oficina',     // 1.3 (Tabla 3)
                'industria_salud',      // 1.4 (Tabla 4)
                'otros'                 // Para Adobe/Madera que suele ser genérico
            ])->index();

            // 3. Estado de Conservación
            $table->enum('estado_conservacion', ['muy_bueno', 'bueno', 'regular', 'malo']);

            // 4. Antigüedad
            $table->integer('antiguedad'); // 0 a 99+

            // RESULTADO: El porcentaje oficial
            $table->decimal('porcentaje', 5, 2);

            $table->timestamps();

            // Clave única compuesta de 4 factores
            $table->unique(['material', 'uso', 'estado_conservacion', 'antiguedad'], 'idx_rnt_exacto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depreciacions');
    }
};
