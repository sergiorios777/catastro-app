<?php

namespace App\Filament\App\Resources\PredioFisicos\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\FusedGroup;
use Filament\Forms\Components\Select;

class PredioFisicoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // SECCIÓN 1: Identificación
                Section::make('Identificación Catastral')
                    ->description('Códigos de identificación del predio')
                    ->columns(2)
                    ->schema([
                        TextInput::make('cuc')
                            ->label('CUC (Código Único Catastral)')
                            ->helperText('Dejar vacío para generar un código provisional interno.')
                            ->maxLength(20)
                            ->unique(ignoreRecord: true), // Valida único por tenant automáticamente

                        TextInput::make('codigo_referencia')
                            ->label('Código de Referencia / Anterior')
                            ->maxLength(255),
                    ]),

                // SECCIÓN 2: Ubicación Física
                Section::make('Ubicación del Predio')
                    ->columns(3)
                    ->schema([
                        TextInput::make('direccion')
                            ->label('Dirección / Vía')
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('distrito')
                            ->default('Distrito Local') // Puedes ajustar esto según la muni
                            ->required(),

                        TextInput::make('sector')
                            ->label('Sector / Zona')
                            ->required(), // Requerido para generar CUC prov.

                        FusedGroup::make()
                            ->columnSpan(1)
                            ->columns(2)
                            ->schema([
                                TextInput::make('manzana')
                                    ->placeholder('Mza.')
                                    ->required()
                                    ->maxLength(10),

                                TextInput::make('lote')
                                    ->placeholder('Lote')
                                    ->required()
                                    ->maxLength(10),
                            ]),
                    ]),

                // SECCIÓN 3: Características
                Section::make('Características Físicas')
                    ->columns(3)
                    ->schema([
                        TextInput::make('area_terreno')
                            ->label('Área de Terreno (m²)')
                            ->numeric()
                            ->prefix('m²')
                            ->required()
                            ->minValue(0),

                        Select::make('tipo_predio')
                            ->options([
                                'urbano' => 'Urbano',
                                'rustico' => 'Rústico',
                            ])
                            ->default('urbano')
                            ->required(),

                        Select::make('estado')
                            ->options([
                                'activo' => 'Activo',
                                'fusionado' => 'Fusionado (Histórico)',
                                'dividido' => 'Dividido (Histórico)',
                                'inactivo' => 'Inactivo',
                            ])
                            ->default('activo')
                            ->required()
                            ->native(false),
                    ]),
            ]);
    }
}
