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
        Schema::create('arancel_rusticos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anio_fiscal_id')->constrained()->cascadeOnDelete();
            $table->string('ubigeo_provincia', 4)->index(); // Ej: '1601' (Loreto - Maynas)

            // 1. Grupo de Tierras (El índice principal)
            $table->char('grupo_tierras', 1); // A, C, P, E

            // 2. Índices Secundarios (Nullables según la regla de negocio)
            // Distancia: Solo para A y C
            $table->enum('distancia', ['hasta_1km', 'de_1_2km', 'de_2_3km', 'mas_3km'])->nullable();

            // Calidad: Solo para A, C, P
            $table->enum('calidad_agrologica', ['alta', 'media', 'baja'])->nullable();

            // 3. El Valor
            $table->decimal('valor_arancel', 10, 4); // Arancel rústico suele tener más decimales (por hectárea)

            $table->timestamps();

            // Aquí no podemos usar unique constraints simples por los nulls, 
            // lo validaremos por software o un índice parcial si usamos PostgreSQL avanzado.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arancel_rusticos');
    }
};
