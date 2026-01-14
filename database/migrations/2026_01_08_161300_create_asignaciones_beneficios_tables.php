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
        // 1. Beneficios aplicados a PERSONAS (Pensionistas, Adulto Mayor)
        // Se descuenta de su base imponible global (las famosas 50 UIT)
        Schema::create('beneficio_personas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained('personas')->cascadeOnDelete();
            $table->foreignId('regla_descuento_tributo_id')->constrained('reglas_descuento_tributos');
            $table->foreignId('tenant_id')->constrained(); // Para saber qué muni otorgó el beneficio

            // Evidencia Legal
            $table->string('documento_resolucion')->comment('N° de Resolución o Expediente que aprueba');
            $table->text('observacion')->nullable();

            // Vigencia de la asignación
            $table->date('valid_from'); // Desde cuándo goza del beneficio
            $table->date('valid_to')->nullable(); // Hasta cuándo (Null = indefinido/vitalicio)

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Beneficios aplicados a PREDIOS (Iglesias, Gobierno, Bomberos)
        // El descuento es sobre el predio específico (Inafectación/Exoneración)
        Schema::create('beneficio_predios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('predio_fisico_id')->constrained('predios_fisicos')->cascadeOnDelete(); // Ojo: A la tabla padre 'predios'
            $table->foreignId('regla_descuento_tributo_id')->constrained('reglas_descuento_tributos');
            $table->foreignId('tenant_id')->constrained();

            $table->string('documento_resolucion');
            $table->text('observacion')->nullable();

            $table->date('valid_from');
            $table->date('valid_to')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones_beneficios_tables');
    }
};
