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
        Schema::create('reglas_descuento_tributos', function (Blueprint $table) {
            $table->id();

            // 1. Identificador de Sistema (Clave para tu Service)
            // Ej: 'PENSIONISTA_50_UIT', 'ADULTO_MAYOR', 'RUSTICO_50_PCT'
            $table->string('codigo')->unique()->index();

            // 2. Descripción Humana
            // Ej: 'Deducción de 50 UIT para Pensionistas'
            $table->string('nombre');

            // 3. Clasificación Legal
            $table->enum('tipo_beneficio', ['inafectacion', 'exoneracion', 'deduccion']);

            // 4. ¿Dónde actúa la matemática? (CRÍTICO)
            // 'base_imponible': Resta al valor del predio (Ej: Pensionista)
            // 'impuesto_calculado': Resta al dinero a pagar (Ej: Beneficio Rústico)
            $table->enum('aplicacion_sobre', ['base_imponible', 'impuesto_calculado']);

            // 5. Valores Matemáticos
            // Cuántas UIT se restan (Ej: 50.00) - Usamos precisión 8,2
            $table->decimal('valor_uit_deducidos', 8, 2)->nullable();

            // Porcentaje de descuento (Ej: 100.00 para inafectos, 50.00 para rústicos)
            $table->decimal('porcentaje_descuento', 5, 2)->nullable();

            // 6. Configuración Avanzada (Opcional pero recomendada)
            // JSON para guardar límites: { "max_ingresos_uit": 1, "max_predios": 1 }
            $table->jsonb('condiciones_param')->nullable();

            // 7. Base Legal y Vigencia
            $table->string('base_legal'); // Ej: 'Art. 19 D.L. 776'
            $table->enum('tipo_tributo', ['predial', 'alcabala', 'arbitrios']);

            $table->date('valid_from');
            $table->date('valid_to')->nullable(); // Null = Indefinido
            $table->boolean('is_active')->default(true);

            // Opcional: Si hay beneficios propios de un municipio específico
            $table->foreignId('tenant_id')->nullable()->constrained();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reglas_descuento_tributos');
    }
};
