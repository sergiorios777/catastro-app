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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // Relaciones Clave
            $table->foreignId('caja_id')->constrained(); // ¿En qué turno se cobró?
            $table->foreignId('determinacion_predial_id')->constrained(); // ¿Qué deuda paga?

            // Datos del Recibo
            $table->string('serie', 4)->default('001'); // Ej: 001
            $table->string('numero', 8);                // Ej: 00001234

            // Detalles del Pago
            $table->decimal('monto_total', 10, 2);
            $table->string('metodo_pago')->default('efectivo'); // efectivo, transferencia, yape, tarjeta
            $table->string('referencia_pago')->nullable(); // Nro de operación si es banco

            $table->dateTime('fecha_pago');
            $table->foreignId('procesado_por')->constrained('users'); // Usuario que hizo clic

            $table->timestamps();

            // Indices para reportes rápidos
            $table->index(['tenant_id', 'fecha_pago']);
            // Evitar duplicar el mismo número de recibo en el mismo tenant
            $table->unique(['tenant_id', 'serie', 'numero']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
