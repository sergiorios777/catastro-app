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
        Schema::create('determinacion_predials', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('persona_id')->constrained()->cascadeOnDelete(); // El Contribuyente
            $table->foreignId('anio_fiscal_id')->constrained()->cascadeOnDelete();

            // Valores Base
            $table->integer('cantidad_predios'); // Ej: 3 predios
            $table->decimal('base_imponible', 15, 2); // La suma de los autoavalúos
            $table->decimal('valor_uit', 10, 2); // Guardamos la UIT usada (auditoría)

            // El Impuesto
            $table->decimal('impuesto_calculado', 12, 2); // El valor bruto
            $table->decimal('tasa_minima', 10, 2)->default(0); // Si aplicó tope mínimo

            // Estado de la Deuda
            $table->enum('estado', ['pendiente', 'pagado', 'fraccionado', 'anulado'])->default('pendiente');
            $table->date('fecha_emision')->nullable();

            $table->timestamps();

            // Un contribuyente solo tiene una determinación por año
            $table->unique(['tenant_id', 'persona_id', 'anio_fiscal_id'], 'unique_det_persona_anio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('determinacion_predials');
    }
};
