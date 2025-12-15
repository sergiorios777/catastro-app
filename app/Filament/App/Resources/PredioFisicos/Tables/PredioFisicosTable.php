<?php

namespace App\Filament\App\Resources\PredioFisicos\Tables;

use App\Models\PredioFisico;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class PredioFisicosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Columna Inteligente de CUC
                TextColumn::make('cuc')
                    ->label('CUC')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(PredioFisico $record): string => $record->es_cuc_provisional ? 'warning' : 'success')
                    ->icon(fn(PredioFisico $record): string => $record->es_cuc_provisional ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-badge')
                    ->tooltip(fn(PredioFisico $record): string => $record->es_cuc_provisional ? 'Código generado internamente (Provisional)' : 'Código Oficial Validado'),

                TextColumn::make('direccion')
                    ->label('Dirección')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('ubicacion_corta')
                    ->label('Mza / Lote')
                    ->state(fn(PredioFisico $record) => "{$record->manzana} - {$record->lote}")
                    ->sortable(['manzana', 'lote']),

                TextColumn::make('area_terreno')
                    ->label('Área')
                    ->numeric(2)
                    ->suffix(' m²')
                    ->sortable(),

                TextColumn::make('tipo_predio')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->color(fn(string $state): string => match ($state) {
                        'urbano' => 'info',
                        'rustico' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'activo' ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('tipo_predio')
                    ->options([
                        'urbano' => 'Urbano',
                        'rustico' => 'Rústico',
                    ]),
                SelectFilter::make('estado')
                    ->default('activo') // Por defecto ver solo activos
                    ->options([
                        'activo' => 'Activo',
                        'fusionado' => 'Fusionado',
                        'dividido' => 'Dividido',
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
