<?php

namespace App\Filament\Resources\CatalogoObraComplementarias\Tables;

use App\Filament\Imports\CatalogoObraComplementariaImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Tables;
use Filament\Tables\Table;

class CatalogoObraComplementariasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('descripcion')
                    ->wrap() // Para que el texto largo no rompa la tabla
                    ->searchable(),

                Tables\Columns\TextColumn::make('unidad_medida')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(CatalogoObraComplementariaImporter::class)
                    ->label('Importar Catálogo')
                    ->icon('heroicon-m-arrow-up-tray'),
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
