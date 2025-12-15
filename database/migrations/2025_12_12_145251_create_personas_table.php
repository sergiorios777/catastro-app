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
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            // Columna de aislamiento (clave foránea)
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // Datos del contribuyente
            $table->string('tipo_persona'); // natural, juridica
            $table->string('tipo_documento'); // DNI, RUC
            $table->string('numero_documento');
            $table->string('nombres')->nullable(); // Para persona natural
            $table->string('apellidos')->nullable(); // Para persona natural
            $table->string('razon_social')->nullable(); // Para jurídica

            // Datos de contacto
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();

            $table->timestamps();

            // Índice único compuesto: 
            // El DNI 12345678 puede existir en el Tenant A y en el Tenant B,
            // pero NO dos veces en el Tenant A.
            $table->unique(['tenant_id', 'tipo_documento', 'numero_documento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
