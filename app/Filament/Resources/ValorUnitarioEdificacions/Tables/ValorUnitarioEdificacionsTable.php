<?php

namespace App\Filament\Resources\ValorUnitarioEdificacions\Tables;

use App\Filament\Imports\ValorUnitarioEdificacionImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ImportAction;
use Filament\Tables;
use Filament\Tables\Table;

class ValorUnitarioEdificacionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Año (Relación)
                Tables\Columns\TextColumn::make('anioFiscal.anio')
                    ->label('Año Fiscal')
                    ->sortable()
                    ->searchable(),

                // 2. Zona (Enum)
                Tables\Columns\TextColumn::make('zona_geografica')
                    ->label('Zona')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'selva' => 'success',
                        'lima_callao' => 'info',
                        'costa' => 'warning',
                        'sierra' => 'danger',
                        default => 'gray',
                    }),

                // 3. Componente (Muros, Techos...)
                Tables\Columns\TextColumn::make('componente')
                    ->formatStateUsing(fn(string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->sortable(),

                // 4. Categoría (A, B, C...)
                Tables\Columns\TextColumn::make('categoria')
                    ->alignCenter()
                    ->weight('bold')
                    ->sortable(),

                // 5. El Precio
                Tables\Columns\TextColumn::make('valor')
                    ->money('PEN')
                    ->sortable(),
            ])
            ->defaultSort('anio_fiscal_id', 'desc')
            ->filters([
                // Filtros útiles para encontrar datos rápido
                Tables\Filters\SelectFilter::make('anioFiscal')
                    ->relationship('anioFiscal', 'anio'),

                Tables\Filters\SelectFilter::make('zona_geografica')
                    ->options([
                        'selva' => 'Selva',
                        'costa' => 'Costa',
                        'sierra' => 'Sierra',
                        'lima_callao' => 'Lima y Callao',
                    ]),

                Tables\Filters\SelectFilter::make('componente')
                    ->options([
                        'muros_columnas' => 'Muros y Columnas',
                        'techos' => 'Techos',
                        'pisos' => 'Pisos',
                        'puertas_ventanas' => 'Puertas y Ventanas',
                        'revestimientos' => 'Revestimientos',
                        'banos' => 'Baños',
                        'inst_electricas_sanitarias' => 'Inst. Eléctricas',
                    ]),
            ])
            ->headerActions([
                // --- AQUÍ ESTÁ EL BOTÓN DE IMPORTACIÓN ---
                ImportAction::make()
                    ->importer(ValorUnitarioEdificacionImporter::class)
                    ->label('Cargar Excel Oficial')
                    ->icon('heroicon-m-arrow-up-tray')
                    ->color('primary'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
