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
            $table->decimal('area_construida', 10, 4)->change();
            $table->decimal('area_comun', 10, 4)->nullable()->after('area_construida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('construccions', function (Blueprint $table) {
            $table->decimal('area_construida', 10, 2)->change();
            $table->dropColumn('area_comun');
        });
    }
};
