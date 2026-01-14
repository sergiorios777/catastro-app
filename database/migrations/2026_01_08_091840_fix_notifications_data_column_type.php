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
        // PostgreSQL: Convertir la columna TEXT a JSON explícitamente
        DB::statement('ALTER TABLE notifications ALTER COLUMN data TYPE JSON USING data::json');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a TEXT si fuera necesario
        DB::statement('ALTER TABLE notifications ALTER COLUMN data TYPE TEXT');
    }
};
