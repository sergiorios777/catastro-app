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
        Schema::create('valor_unitario_edificacions', function (Blueprint $table) {
            $table->id();
            // 1. Pertenencia
            // $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('anio_fiscal_id')->constrained()->cascadeOnDelete();

            // 2. Clasificación
            // Guardamos la zona para ser explícitos (ej: 'selva'), aunque el tenant ya lo tenga.
            $table->enum('zona_geografica', ['lima_callao', 'costa', 'sierra', 'selva']);

            // Los 7 componentes estructurales (según reglamento nacional)
            $table->enum('componente', [
                'muros_columnas',
                'techos',
                'pisos',
                'puertas_ventanas',
                'revestimientos',
                'banos',
                'inst_electricas_sanitarias'
            ]);

            $table->char('categoria', 1); // A, B, C... J

            // 3. El dinero
            $table->decimal('valor', 10, 2); // El precio por m2

            $table->timestamps();

            // 4. Candado de unicidad
            // No puede haber dos precios para "Muros A en Selva para el 2025"
            $table->unique(
                ['anio_fiscal_id', 'zona_geografica', 'componente', 'categoria'],
                'unique_valor_unitario' // Nombre corto para el índice
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valor_unitario_edificacions');
    }
};
