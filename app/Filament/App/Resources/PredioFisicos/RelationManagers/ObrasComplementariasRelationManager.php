<?php

namespace App\Filament\App\Resources\PredioFisicos\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables;

class ObrasComplementariasRelationManager extends RelationManager
{
    protected static string $relationship = 'obrasComplementarias';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('descripcion')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('descripcion')
            ->columns([
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Tipo de Obra')
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('unidad_medida')
                    ->label('Und.')
                    ->badge(),

                // Columnas de la Tabla Pivote
                Tables\Columns\TextColumn::make('pivot.cantidad')
                    ->label('Cantidad')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('pivot.anio_construccion')
                    ->label('Antigüedad')
                    ->formatStateUsing(fn($state) => now()->year - $state . ' años'),

                Tables\Columns\TextColumn::make('pivot.estado_conservacion')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'muy_bueno' => 'success',
                        'bueno' => 'info',
                        'regular' => 'warning',
                        'malo' => 'danger',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Usamos Attach para seleccionar del Catálogo existente
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->label('Agregar Obra')
                    ->modalHeading('Vincular Obra Complementaria')
                    ->recordSelectSearchColumns(['codigo', 'descripcion'])
                    ->form(fn(AttachAction $action): array => [
                        $action->getRecordSelect(), // El buscador del catálogo

                        // Campos Específicos (Pivote)
                        TextInput::make('cantidad')
                            ->label('Cantidad / Dimensión')
                            ->numeric()
                            ->required(),

                        TextInput::make('anio_construccion')
                            ->label('Año Construcción')
                            ->numeric()
                            ->maxValue(now()->year)
                            ->required(),

                        Select::make('estado_conservacion')
                            ->options([
                                'muy_bueno' => 'Muy Bueno',
                                'bueno' => 'Bueno',
                                'regular' => 'Regular',
                                'malo' => 'Malo',
                            ])
                            ->default('regular')
                            ->required(),
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->form([
                        TextInput::make('cantidad')->numeric()->required(),
                        TextInput::make('anio_construccion')->numeric()->required(),
                        Select::make('estado_conservacion')
                            ->options([
                                'muy_bueno' => 'Muy Bueno',
                                'bueno' => 'Bueno',
                                'regular' => 'Regular',
                                'malo' => 'Malo',
                            ])
                            ->required(),
                    ]),
                DetachAction::make(),
                // DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
