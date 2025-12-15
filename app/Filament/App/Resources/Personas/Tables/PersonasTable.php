<?php

namespace App\Filament\App\Resources\Personas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class PersonasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_documento')
                    ->searchable()
                    ->sortable()
                    ->label('Documento'),

                TextColumn::make('nombre_completo')
                    ->label('Nombre / Razón Social')
                    ->searchable(['nombres', 'apellidos', 'razon_social'])
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('apellidos', $direction)
                            ->orderBy('razon_social', $direction);
                    }),

                TextColumn::make('tipo_persona')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'natural' => 'success',
                        'juridica' => 'info',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),

                TextColumn::make('direccion')
                    ->label('Dirección')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('telefono')
                    ->icon('heroicon-m-phone')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo_persona')
                    ->options([
                        'natural' => 'Natural',
                        'juridica' => 'Jurídica',
                    ]),
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
