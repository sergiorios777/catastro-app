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
        Schema::table('construccions', function (Blueprint $table) {
            // Uso específico de ESTA construcción (para buscar en la tabla correcta)
            $table->enum('uso_especifico', [
                'casa_habitacion',
                'tienda_deposito',
                'edificio_oficina',
                'industria_salud',
                'otros'
            ])->default('casa_habitacion')->after('material_estructural');

            // Porcentaje calculado automáticamente (se guarda para historial)
            $table->decimal('porcentaje_depreciacion_calculado', 5, 2)->nullable();

            // Porcentaje manual (si el usuario quiere corregir el RNT)
            $table->decimal('porcentaje_depreciacion_manual', 5, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('construccions', function (Blueprint $table) {
            $table->dropColumn('uso_especifico');
            $table->dropColumn('porcentaje_depreciacion_calculado');
            $table->dropColumn('porcentaje_depreciacion_manual');
        });
    }
};
