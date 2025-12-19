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
        Schema::table('determinacion_predials', function (Blueprint $table) {
            // Aquí guardaremos la "foto" completa de los predios al momento del cálculo
            $table->jsonb('snapshot_datos')->nullable()->after('tasa_minima');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('determinacion_predials', function (Blueprint $table) {
            $table->dropColumn('snapshot_datos');
        });
    }
};
