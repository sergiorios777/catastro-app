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
            // --- URBANO ---
            // Deben coincidir con los ENUM de arancel_urbanos
            $table->string('tipo_calzada')->nullable()->comment('tierra, afirmado, empedrado, asfalto, concreto');
            $table->string('ancho_via')->nullable()->comment('menos_6, entre_6_8, mas_8');

            $table->boolean('tiene_agua')->default(false);
            $table->boolean('tiene_desague')->default(false);
            $table->boolean('tiene_luz')->default(false);

            // --- RÚSTICO ---
            // Deben coincidir con los ENUM de arancel_rusticos
            $table->char('grupo_tierras', 1)->nullable()->comment('A: Arables, C: Cultivo, P: Pastos, E: Eriazas');
            $table->string('distancia')->nullable()->comment('hasta_1km, de_1_2km, de_2_3km, mas_3km');
            $table->string('calidad_agrologica')->nullable()->comment('alta, media, baja');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predios_fisicos', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_calzada',
                'ancho_via',
                'tiene_agua',
                'tiene_desague',
                'tiene_luz',
                'grupo_tierras',
                'distancia',
                'calidad_agrologica'
            ]);
        });
    }
};
