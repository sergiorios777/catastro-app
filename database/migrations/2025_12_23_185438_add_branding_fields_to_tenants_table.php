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
            $table->string('ruc', 11)->nullable()->after('name');
            $table->string('direccion_fiscal')->nullable()->after('ruc');
            $table->string('slogan')->nullable()->after('name');
            $table->string('logo')->nullable()->after('slogan'); // Para guardar la ruta de la imagen
            $table->string('web')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['ruc', 'direccion_fiscal', 'slogan', 'logo', 'web']);
        });
    }
};
