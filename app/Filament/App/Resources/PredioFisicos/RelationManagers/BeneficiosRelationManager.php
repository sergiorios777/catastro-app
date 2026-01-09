<?php

namespace App\Filament\App\Resources\PredioFisicos\RelationManagers;

use App\Models\ReglasDescuentoTributo;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ToggleColumn;
use BackedEnum;

class BeneficiosRelationManager extends RelationManager
{
    protected static string $relationship = 'beneficios';
    protected static ?string $title = 'Exoneraciones o inafectaciones';
    protected static string|BackedEnum|null $icon = 'heroicon-o-identification';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)->schema([
                    Select::make('regla_descuento_tributo_id')
                        ->label('Regla Aplicable')
                        ->options(function () {
                            // Filtramos exoneraciones e inafectaciones (Predios)
                            return ReglasDescuentoTributo::whereIn('tipo_beneficio', ['exoneracion', 'inafectacion'])
                                ->where('is_active', true)
                                ->pluck('nombre', 'id');
                        })
                        ->required()
                        ->searchable()
                        ->preload(),

                    TextInput::make('documento_resolucion')
                        ->label('Documento de Sustento')
                        ->required(),

                    Grid::make(2)->schema([
                        DatePicker::make('valid_from')
                            ->label('Vigente Desde')
                            ->default(now())
                            ->required(),
                        DatePicker::make('valid_to')
                            ->label('Hasta')
                            ->placeholder('Indefinido'),
                    ]),

                    Textarea::make('observacion')
                        ->rows(2),

                    Toggle::make('is_active')
                        ->label('Activo')
                        ->default(true),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('documento_resolucion')
            ->columns([
                TextColumn::make('reglaDescuentoTributo.nombre')
                    ->label('Exoneración')
                    ->badge()
                    ->color(fn($record) => $record->reglaDescuentoTributo->tipo_beneficio === 'inafectacion' ? 'gray' : 'success'),

                TextColumn::make('reglaDescuentoTributo.porcentaje_descuento')
                    ->label('% Dscto')
                    ->suffix('%'),

                TextColumn::make('documento_resolucion'),

                ToggleColumn::make('is_active')->label('Activo'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
