<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AnioFiscal;

class AnioFiscalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $anios = [
            [
                'anio' => 2020,
                'valor_uit' => 4300.00,
                'costo_emision' => 3.40,
                'tasa_minima_predial' => 12.90,
                'activo' => false,
            ],
            [
                'anio' => 2021,
                'valor_uit' => 4400.00,
                'costo_emision' => 3.60,
                'tasa_minima_predial' => 13.20,
                'activo' => false,
            ],
            [
                'anio' => 2022,
                'valor_uit' => 4600.00,
                'costo_emision' => 4.50,
                'tasa_minima_predial' => 13.80,
                'activo' => false,
            ],
            [
                'anio' => 2023,
                'valor_uit' => 4950.00,
                'costo_emision' => 5.20,
                'tasa_minima_predial' => 14.85,
                'activo' => false,
            ],
            [
                'anio' => 2024,
                'valor_uit' => 5150.00,
                'costo_emision' => 6.80,
                'tasa_minima_predial' => 15.45,
                'activo' => false,
            ],
            [
                'anio' => 2025,
                'valor_uit' => 5350.00, // Valor oficial 2025
                'costo_emision' => 7.50,
                'tasa_minima_predial' => 16.05,
                'activo' => false, // Lo dejamos falso para activar el 2026
            ],
            [
                'anio' => 2026,
                'valor_uit' => 5550.00, // Proyección (ajustar cuando sea oficial)
                'costo_emision' => 8.00,
                'tasa_minima_predial' => 16.65,
                'activo' => true, // El año actual/futuro activo
            ],
        ];

        foreach ($anios as $data) {
            // Usamos el modelo si existe
            if (class_exists(AnioFiscal::class)) {
                AnioFiscal::updateOrCreate(
                    ['anio' => $data['anio']], // Busca por año
                    [
                        'valor_uit' => $data['valor_uit'],
                        'tasa_ipm' => 0, // Valor por defecto según tu SQL
                        'costo_emision' => $data['costo_emision'],
                        'tasa_minima_predial' => $data['tasa_minima_predial'],
                        'activo' => $data['activo'],
                        'factor_oficializacion' => 0.68, // Valor por defecto según tu SQL
                    ]
                );
            } else {
                // Fallback usando DB Facade si no has creado el Modelo aún
                // Nota: updateOrCreate no existe en DB facade directamente de esta forma simple,
                // así que usamos insertOrIgnore o upsert
                DB::table('anio_fiscals')->upsert(
                    array_merge($data, [
                        'tasa_ipm' => 0,
                        'factor_oficializacion' => 0.68,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]),
                    ['anio'], // Columna única
                    ['valor_uit', 'costo_emision', 'tasa_minima_predial', 'activo']
                );
            }
        }
    }
}
