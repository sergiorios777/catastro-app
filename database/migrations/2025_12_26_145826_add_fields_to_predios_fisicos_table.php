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
        Schema::table('predios_fisicos', function (Blueprint $table) {
            /*
              info_complementaria:
              {
                "clasificacion_predio": {
                    "otro": {
                        "otro": "clínica",
                        }
                },
                "ubicacion_predio": {
                    "otro": {
                        "otro": "mini puerto",
                        }
                },
                "area_verificada": 120.50,
                "linderos": {
                    "frente": { "medida": 10, "colindancia": "Calle Real" },
                    "derecha": { "medida": 20, "colindancia": "Lote 4" },
                    "izquierda": { "medida": 20.4, "colindancia": "Lote 4" },
                    "fondo": { "medida": 11.2, "colindancia": "Lote 4" },
                },
                "servicios_extra": {
                    "telefono": true,
                    "gas": true,
                    "internet": false,
                    "cable": true
                }
              }
             */
            $table->jsonb('info_complementaria')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predios_fisicos', function (Blueprint $table) {
            $table->dropColumn('info_complementaria');
        });
    }
};
