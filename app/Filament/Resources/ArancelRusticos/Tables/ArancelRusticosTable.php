<?php

namespace App\Filament\Resources\ArancelRusticos\Tables;

use App\Filament\Imports\ArancelRusticoImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;

class ArancelRusticosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Ubicación Temporal y Espacial
                TextColumn::make('anioFiscal.anio')
                    ->label('Año')
                    ->sortable()
                    ->width('100px'),

                TextColumn::make('ubigeo_provincia')
                    ->label('Provincia')
                    ->searchable()
                    ->description(fn($state) => match (substr($state, 0, 4)) {
                        '1601' => 'Maynas', // Ejemplos comunes en Loreto
                        '1602' => 'Alto Amazonas',
                        '1603' => 'Loreto',
                        '1604' => 'Mariscal Ramón Castilla',
                        '1605' => 'Putumayo',
                        '1606' => 'Requena',
                        '1607' => 'Ucayali',
                        '1608' => 'Datem del Marañón',
                        default => 'Ubigeo ' . $state,
                    }),

                // 2. Grupo de Tierras (El dato principal)
                TextColumn::make('grupo_tierras')
                    ->label('Grupo')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'A' => 'success', // Cultivo en Limpio (Verde)
                        'C' => 'info',    // Permanente (Azul)
                        'P' => 'warning', // Pastos (Amarillo)
                        'E' => 'danger',  // Eriazo (Rojo)
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'A' => 'A - Cultivo en Limpio',
                        'C' => 'C - Cultivo Permanente',
                        'P' => 'P - Pastos',
                        'E' => 'E - Eriazo',
                        default => $state,
                    })
                    ->sortable(),

                // 3. Detalles (Manejo de vacíos)
                TextColumn::make('distancia')
                    ->label('Distancia')
                    ->placeholder('-') // Muestra guion si es null (Eriazos/Pastos)
                    ->formatStateUsing(fn(string $state): string => ucfirst(str_replace('_', ' ', $state))),

                TextColumn::make('calidad_agrologica')
                    ->label('Calidad')
                    ->placeholder('-')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'alta' => 'success',
                        'media' => 'warning',
                        'baja' => 'danger',
                        default => 'gray',
                    }),

                // 4. El Precio (Con 4 decimales para precisión rústica)
                TextColumn::make('valor_arancel')
                    ->label('Valor Ha/m²')
                    ->money('PEN')
                    ->weight('bold'),
            ])
            ->filters([
                SelectFilter::make('anioFiscal')->relationship('anioFiscal', 'anio'),

                SelectFilter::make('grupo_tierras')
                    ->options([
                        'A' => 'A - Aptas para Cultivo en Limpio',
                        'C' => 'C - Aptas para Cultivo Permanente',
                        'P' => 'P - Aptas para Pastos',
                        'E' => 'E - Tierras Eriazas',
                    ]),

                SelectFilter::make('ubigeo_provincia')
                    ->label('Provincia')
                    ->options([
                        '1601' => 'Maynas',
                        '1605' => 'Putumayo',
                        // Agrega las que necesites filtrar frecuentemente
                    ]),
            ])
            ->headerActions([
                // --- AQUÍ ESTÁ EL BOTÓN DE IMPORTACIÓN ---
                ImportAction::make()
                    ->importer(ArancelRusticoImporter::class)
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
