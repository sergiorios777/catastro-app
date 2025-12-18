<?php

namespace App\Filament\Resources\ArancelUrbanos\Tables;

use App\Filament\Imports\ArancelUrbanoImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Tables;
use Filament\Tables\Table;

class ArancelUrbanosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Ubicación
                Tables\Columns\TextColumn::make('anioFiscal.anio')
                    ->label('Año')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ubigeo_distrito')
                    ->label('Ubigeo')
                    ->searchable(),

                // 2. Características Viales
                Tables\Columns\TextColumn::make('tipo_calzada')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'concreto', 'asfalto' => 'success',
                        'tierra' => 'danger',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('ancho_via')
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'menos_6' => '< 6m',
                        'entre_6_8' => '6m - 8m',
                        'mas_8' => '> 8m',
                        default => $state,
                    }),

                // 3. Servicios (Iconos en lugar de texto 1/0)
                Tables\Columns\IconColumn::make('tiene_agua')
                    ->label('Agua')
                    ->boolean(),

                Tables\Columns\IconColumn::make('tiene_desague')
                    ->label('Desagüe')
                    ->boolean(),

                Tables\Columns\IconColumn::make('tiene_luz')
                    ->label('Luz')
                    ->boolean(),

                // 4. El Precio
                Tables\Columns\TextColumn::make('valor_arancel')
                    ->label('Arancel x m²')
                    ->money('PEN')
                    ->weight('bold')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('anioFiscal')->relationship('anioFiscal', 'anio'),
                Tables\Filters\SelectFilter::make('tipo_calzada')
                    ->options([
                        'tierra' => 'Tierra',
                        'afirmado' => 'Afirmado',
                        'empedrado' => 'Empedrado',
                        'asfalto' => 'Asfalto',
                        'concreto' => 'Concreto',
                    ]),
            ])
            ->headerActions([
                // --- AQUÍ ESTÁ EL BOTÓN DE IMPORTACIÓN ---
                ImportAction::make()
                    ->importer(ArancelUrbanoImporter::class)
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
