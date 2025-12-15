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
        Schema::table('predios_fisicos', function (Blueprint $table) {
            // Bandera para saber el origen del código
            $table->boolean('es_cuc_provisional')->default(false)->after('cuc');

            // Hacemos el CUC nullable explícitamente si no lo era, 
            // aunque ya lo definimos así, es bueno asegurar.
            // (En la migración anterior ya era nullable, así que no es estrictamente necesario cambiarlo,
            // pero aseguramos la lógica).
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predios_fisicos', function (Blueprint $table) {
            $table->dropColumn('es_cuc_provisional');
        });
    }
};
