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
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(); // El cajero responsable

            // Control de Tiempos
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();

            // Control de Dinero
            $table->decimal('monto_apertura', 10, 2)->default(0); // Sencillo inicial
            $table->decimal('monto_cierre', 10, 2)->nullable();   // Arqueo final
            $table->decimal('total_recaudado', 10, 2)->default(0); // Suma automática de ingresos

            // Estado
            $table->enum('estado', ['abierta', 'cerrada', 'arqueada'])->default('abierta');
            $table->string('observaciones')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
