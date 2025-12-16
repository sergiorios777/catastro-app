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
        Schema::table('anio_fiscals', function (Blueprint $table) {
            $table->decimal('factor_oficializacion', 5, 2)
                ->default(0.68) // Valor actual según RM
                ->after('valor_uit')
                ->comment('Factor para obras complementarias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        schema::table('anio_fiscals', function (Blueprint $table) {
            $table->dropColumn('factor_oficializacion');
        });
    }
};
