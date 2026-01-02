<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // AJUSTA EL NOMBRE DE LA TABLA SI ES NECESARIO (ej: 'predio_fisicos')
        Schema::table('predios_fisicos', function (Blueprint $table) {
            $table->uuid('track_id')->nullable()->index()->after('id');
            $table->integer('version')->default(1)->after('track_id');
            $table->boolean('is_active')->default(true)->index();
            $table->dateTime('valid_from')->nullable()->useCurrent();
            $table->dateTime('valid_to')->nullable();
        });

        // Llenar track_id para los predios que ya existen
        DB::statement("UPDATE predios_fisicos SET track_id = GEN_RANDOM_UUID() WHERE track_id IS NULL");
    }

    public function down(): void
    {
        Schema::table('predios_fisicos', function (Blueprint $table) {
            $table->dropColumn(['track_id', 'version', 'is_active', 'valid_from', 'valid_to']);
        });
    }
};
