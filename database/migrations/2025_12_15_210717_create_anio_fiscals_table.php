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
        Schema::create('anio_fiscals', function (Blueprint $table) {
            $table->id();
            // Importante: Multi-tenancy
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // Datos del Año
            $table->integer('anio'); // Ej: 2025
            $table->decimal('valor_uit', 10, 2); // Ej: 5350.00

            // Parámetros Tributarios
            $table->decimal('tasa_ipm', 5, 2)->default(0.00); // Impuesto Promoción Municipal (usualmente incluido en la tasa total)
            $table->decimal('costo_emision', 10, 2)->default(0.00); // Derecho de Emisión Mecanizada (lo que cobran por imprimir la cuponera)
            $table->decimal('tasa_minima_predial', 10, 2)->nullable(); // Por si queremos configurar la tasa base (0.2%)

            // Estado
            $table->boolean('activo')->default(false); // Solo un año debería estar activo para operaciones actuales

            $table->timestamps();

            // Evitar duplicar el año 2025 para el mismo municipio
            $table->unique(['tenant_id', 'anio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anio_fiscals');
    }
};
