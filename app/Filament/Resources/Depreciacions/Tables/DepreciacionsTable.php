<?php

namespace App\Filament\Resources\Depreciacions\Tables;

use App\Filament\Imports\DepreciacionImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;

class DepreciacionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Material Estructural
                TextColumn::make('material')
                    ->label('Material')
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'concreto', 'ladrillo' => 'gray', // Materiales nobles
                        'adobe', 'madera' => 'warning',   // Materiales vulnerables
                        default => 'info',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),

                // 2. Uso (Clasificación RNT)
                TextColumn::make('uso')
                    ->label('Clasificación / Uso')
                    ->sortable()
                    ->description(fn(string $state): string => match ($state) {
                        'casa_habitacion' => 'Tabla 1 (Vivienda)',
                        'tienda_deposito' => 'Tabla 2 (Comercio)',
                        'edificio_oficina' => 'Tabla 3 (Oficinas)',
                        'industria_salud' => 'Tabla 4 (Salud/Ind.)',
                        'otros' => 'Genérico',
                        default => '',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'casa_habitacion' => 'Casa Habitación / Depto',
                        'tienda_deposito' => 'Tiendas / Depósitos',
                        'edificio_oficina' => 'Edificios / Oficinas',
                        'industria_salud' => 'Clínicas / Industrias',
                        'otros' => 'Otros Fines',
                        default => $state,
                    })
                    ->wrap(), // Permite que el texto largo baje de línea

                // 3. Estado
                TextColumn::make('estado_conservacion')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'muy_bueno' => 'success',
                        'bueno' => 'info',
                        'regular' => 'warning',
                        'malo' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst(str_replace('_', ' ', $state))),

                // 4. Antigüedad
                TextColumn::make('antiguedad')
                    ->label('Antigüedad')
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(function (string $state) {
                        if ($state > 50) {
                            return "Más de 50 años";
                        }
                        return "Hasta {$state} años";
                    }),

                // 5. El Porcentaje (Dato Clave)
                TextColumn::make('porcentaje')
                    ->label('% Depr.')
                    ->sortable()
                    ->numeric(2)
                    ->suffix('%')
                    ->weight('bold')
                    ->color(fn(string $state): string => $state > 50 ? 'danger' : 'success'), // Rojo si deprecia mucho
            ])
            ->filters([
                // Filtros para navegar rápido la matriz
                SelectFilter::make('material')
                    ->options([
                        'concreto' => 'Concreto',
                        'ladrillo' => 'Ladrillo',
                        'adobe' => 'Adobe',
                        'madera' => 'Madera',
                    ]),

                SelectFilter::make('uso')
                    ->label('Uso RNT')
                    ->options([
                        'casa_habitacion' => '1.1 Vivienda',
                        'tienda_deposito' => '1.2 Tiendas',
                        'edificio_oficina' => '1.3 Oficinas',
                        'industria_salud' => '1.4 Industria',
                    ]),

                SelectFilter::make('estado_conservacion')
                    ->options([
                        'muy_bueno' => 'Muy Bueno',
                        'bueno' => 'Bueno',
                        'regular' => 'Regular',
                        'malo' => 'Malo',
                    ]),
            ], layout: FiltersLayout::AboveContent) // Pone los filtros arriba horizontalmente (más cómodo)
            ->headerActions([
                // --- AQUÍ ESTÁ EL BOTÓN DE IMPORTACIÓN ---
                ImportAction::make()
                    ->importer(DepreciacionImporter::class)
                    ->label('Cargar CSV')
                    ->icon('heroicon-m-arrow-up-tray')
                    ->color('primary'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
