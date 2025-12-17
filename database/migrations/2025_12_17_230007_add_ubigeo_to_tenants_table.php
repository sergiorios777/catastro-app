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
            // El código clave (Ej: '160506' para Rosa Panduro/Putumayo)
            $table->string('ubigeo', 6)->nullable()->index()->after('id');

            // Información legible para reportes
            $table->string('departamento')->nullable()->after('ubigeo');
            $table->string('provincia')->nullable()->after('departamento');
            $table->string('distrito')->nullable()->after('provincia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['ubigeo', 'departamento', 'provincia', 'distrito']);
        });
    }
};
