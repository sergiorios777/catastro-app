<?php

namespace App\Filament\App\Resources\AnioFiscals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class AnioFiscalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('anio')
                    ->label('Año Fiscal')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'), // Resalta el año

                TextColumn::make('valor_uit')
                    ->label('Valor UIT')
                    ->money('PEN') // Formatea automáticamente con "S/ "
                    ->sortable(),

                TextColumn::make('tasa_ipm')
                    ->label('Tasa IPM')
                    ->suffix('%')
                    ->alignCenter(),

                TextColumn::make('costo_emision')
                    ->label('Derecho Emisión')
                    ->money('PEN'),

                TextColumn::make('activo')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? 'ACTIVO' : 'INACTIVO')
                    ->color(fn(bool $state): string => $state ? 'success' : 'gray')
                    ->icon(fn(bool $state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-minus-circle'),
            ])
            ->defaultSort('anio', 'desc') // Ordenar por año descendente (el más reciente primero)
            ->filters([
                //
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
