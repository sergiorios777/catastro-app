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
        Schema::table('tenants', function (Blueprint $table) {
            // Usamos un enum para garantizar integridad. 
            // 'costa' incluye Lima/Callao para simplificar, o sepáralo si la norma de precios lo exige distinto.
            $table->enum('zona_geografica', ['lima_callao', 'costa', 'sierra', 'selva'])
                ->default('selva');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('zona_geografica');
        });
    }
};
