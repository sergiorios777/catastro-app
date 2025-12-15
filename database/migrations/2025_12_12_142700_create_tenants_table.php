<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // Para el subdominio: loreto.app.com [cite: 73]
            $table->string('status')->default('active'); // active, suspended [cite: 74]
            $table->json('settings')->nullable(); // Configuración visual/logo por municipio [cite: 76]
            $table->boolean('subscription_active')->default(true); // [cite: 75]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
