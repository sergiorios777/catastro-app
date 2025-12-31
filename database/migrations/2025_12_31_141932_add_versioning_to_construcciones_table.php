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
            // Identificador único del "objeto" real a través del tiempo
            // Si ya tienes un código único de construcción, úsalo. Si no, usaremos un UUID o similar.
            $table->uuid('track_id')->nullable()->index()->after('id');

            // Control de versiones
            $table->integer('version')->default(1)->after('track_id');
            $table->boolean('is_active')->default(true)->index(); // Para búsquedas rápidas

            // Vigencia Fiscal
            $table->date('valid_from')->nullable(); // Desde cuándo aplica
            $table->date('valid_to')->nullable();   // Hasta cuándo aplicó (NULL = actual)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('construccions', function (Blueprint $table) {
            $table->dropColumn(['track_id', 'version', 'is_active', 'valid_from', 'valid_to']);
        });
    }
};
