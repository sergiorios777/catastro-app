<?php

namespace App\Filament\Resources\ReglaDescuentoTributos\Tables;

use App\Models\ReglasDescuentoTributo;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class ReglaDescuentoTributosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->fontFamily('mono'),

                TextColumn::make('nombre')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('tipo_beneficio')
                    ->badge()
                    ->colors([
                        'warning' => 'deduccion',
                        'success' => 'exoneracion',
                        'gray' => 'inafectacion',
                    ]),

                TextColumn::make('valores')
                    ->label('Valor')
                    ->state(function (ReglasDescuentoTributo $record) {
                        if ($record->tipo_beneficio === 'deduccion') {
                            return "- {$record->valor_uit_deducidos} UIT";
                        }
                        return "- {$record->porcentaje_descuento}%";
                    }),

                TextColumn::make('valid_from')
                    ->label('Vigencia')
                    ->date('d/m/Y')
                    ->description(fn($record) => $record->valid_to ? 'Hasta ' . $record->valid_to->format('d/m/Y') : 'Indefinido'),

                ToggleColumn::make('is_active')
                    ->label('Activo'),
            ])
            ->filters([
                SelectFilter::make('tipo_tributo')
                    ->options([
                        'predial' => 'Predial',
                        'alcabala' => 'Alcabala',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Estado'),
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
