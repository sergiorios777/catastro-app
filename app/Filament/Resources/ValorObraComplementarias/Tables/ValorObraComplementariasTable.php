<?php

namespace App\Filament\Resources\ValorObraComplementarias\Tables;

use App\Filament\Imports\ValorObraComplementariaImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Tables;
use Filament\Tables\Table;

class ValorObraComplementariasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('anioFiscal.anio')->label('Año')->sortable(),
                Tables\Columns\TextColumn::make('obra.codigo')->label('Cód.')->searchable(),
                Tables\Columns\TextColumn::make('obra.descripcion')->label('Obra')->limit(50),
                Tables\Columns\TextColumn::make('zona_geografica')->badge(),
                Tables\Columns\TextColumn::make('valor')->money('PEN'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('anioFiscal')->relationship('anioFiscal', 'anio'),
                Tables\Filters\SelectFilter::make('zona_geografica')
                    ->options(['selva' => 'Selva', 'costa' => 'Costa', 'sierra' => 'Sierra', 'lima_callao' => 'Lima']),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(ValorObraComplementariaImporter::class)
                    ->label('Importar Precios Obras')
                    ->icon('heroicon-m-arrow-up-tray'),
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
