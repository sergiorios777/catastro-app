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
        Schema::create('valor_obra_complementarias', function (Blueprint $table) {
            $table->id();

            // Relaciones Globales
            $table->foreignId('anio_fiscal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('catalogo_obra_complementaria_id')
                ->constrained('catalogo_obra_complementarias')
                ->cascadeOnDelete()
                ->name('fk_valor_obra_cat_id'); // Nombre corto para evitar errores de longitud

            // Clasificación
            $table->enum('zona_geografica', ['lima_callao', 'costa', 'sierra', 'selva']);

            // El Dinero
            $table->decimal('valor', 10, 2);

            $table->timestamps();

            // Evitar duplicados: 
            // Para un año, una zona y un ítem específico, solo hay un precio.
            $table->unique(
                ['anio_fiscal_id', 'catalogo_obra_complementaria_id', 'zona_geografica'],
                'unique_valor_obra_comp'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valor_obra_complementarias');
    }
};
