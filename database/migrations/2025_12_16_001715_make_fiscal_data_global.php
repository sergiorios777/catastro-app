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
        // 1. Modificar AnioFiscal
        Schema::table('anio_fiscals', function (Blueprint $table) {
            // Eliminamos la llave foránea y la columna
            $table->dropForeign(['tenant_id']);
            $table->dropUnique(['tenant_id', 'anio']); // Eliminamos la restricción única anterior
            $table->dropColumn('tenant_id');

            // Nueva restricción: Solo puede haber un año 2025 en todo el sistema
            $table->unique('anio');
        });

        // 2. Modificar ValorUnitarioEdificacion
        Schema::table('valor_unitario_edificacions', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            // Eliminamos el índice único viejo que incluía tenant_id
            $table->dropUnique('unique_valor_unitario');
            $table->dropColumn('tenant_id');

            // Nuevo índice único global:
            // En todo el sistema, para el año X, zona Y, componente Z y categoría W, solo existe UN precio.
            $table->unique(
                ['anio_fiscal_id', 'zona_geografica', 'componente', 'categoria'],
                'global_unique_valor_unitario'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
