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
        Schema::create('catalogo_obra_complementarias', function (Blueprint $table) {
            $table->id();

            // Datos Maestros (Sin Tenant, es Global)
            $table->string('codigo')->unique(); // Ej: '01.01'
            $table->text('descripcion'); // Ej: 'Cercos de ladrillo...'
            $table->string('unidad_medida'); // Ej: 'm2', 'ml', 'und', 'gl'

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogo_obra_complementarias');
    }
};
