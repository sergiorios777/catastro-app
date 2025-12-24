<?php

namespace App\Filament\App\Resources\PredioFisicos\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

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
                            ->label('Manzana y Lote')
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
                            ->label('Clasificación del Predio')
                            ->options([
                                'urbano' => 'Urbano',
                                'rustico' => 'Rústico',
                            ])
                            ->default('urbano')
                            ->required()
                            ->live(), // <--- VITAL: Recarga el formulario al cambiar

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

                // SECCIÓN 4: Características y Arancel
                Section::make('Características para Arancel')
                    ->description('Estos datos determinan el valor por m² del terreno.')
                    ->schema([
                        // 2. CAMPOS URBANOS (Solo visibles si es Urbano)
                        Grid::make(2)
                            ->visible(fn(Get $get) => $get('tipo_predio') === 'urbano')
                            ->schema([
                                Select::make('tipo_calzada')
                                    ->options([
                                        // Keys IDÉNTICAS al ENUM de BD
                                        'tierra' => 'Tierra',
                                        'afirmado' => 'Afirmado',
                                        'empedrado' => 'Empedrado',
                                        'asfalto' => 'Asfalto',
                                        'concreto' => 'Concreto',
                                    ])
                                    ->required(),

                                Select::make('ancho_via')
                                    ->options([
                                        // Keys IDÉNTICAS al ENUM de BD
                                        'menos_6' => 'Menos de 6.00 ml',
                                        'entre_6_8' => 'Entre 6.00 y 8.00 ml',
                                        'mas_8' => 'Más de 8.00 ml',
                                    ])
                                    ->required(),

                            ]),

                        // 3. CAMPOS RÚSTICOS (Solo visibles si es Rústico)
                        Grid::make(3)
                            ->visible(fn(Get $get) => $get('tipo_predio') === 'rustico')
                            ->schema([
                                Select::make('grupo_tierras')
                                    ->label('Grupo de Tierras')
                                    ->options([
                                        // Keys IDÉNTICAS al CHAR(1) de BD
                                        'A' => 'Tierras Aptas para Cultivo (A)',
                                        'C' => 'Tierras para Cultivo Permanente (C)',
                                        'P' => 'Pastos (P)',
                                        'X' => 'Eriazas (X)',
                                    ])
                                    ->required(),

                                Select::make('distancia')
                                    ->label('Distancia a Vías/Ríos')
                                    ->options([
                                        // Keys IDÉNTICAS al ENUM de BD
                                        'hasta_1km' => 'Hasta 1.00 km',
                                        'de_1_2km' => 'Más de 1.00 hasta 2.00 km',
                                        'de_2_3km' => 'Más de 2.00 hasta 3.00 km',
                                        'mas_3km' => 'Más de 3.00 km',
                                    ])
                                    // OJO: Según tu schema, distancia puede ser NULL en algunos grupos de tierras.
                                    // Aquí lo dejo required para simplificar, pero podrías condicionarlo.
                                    ->required(),

                                Select::make('calidad_agrologica')
                                    ->label('Calidad del Suelo')
                                    ->options([
                                        'alta' => 'Alta',
                                        'media' => 'Media',
                                        'baja' => 'Baja',
                                    ])->required(),
                            ]),

                        // Grupo de Checkboxes para servicios
                        Section::make('Servicios Básicos')
                            ->columnSpan(1)
                            ->compact()
                            ->columns(3)
                            ->schema([
                                Toggle::make('tiene_luz')->label('Luz')->inline(false),
                                Toggle::make('tiene_agua')->label('Agua')->inline(false),
                                Toggle::make('tiene_desague')->label('Desagüe')->inline(false),
                            ]),

                        // El Área siempre va
                        /*TextInput::make('area_terreno')
                            ->label('Área Total (m²)')
                            ->numeric()
                            ->required()
                            ->minValue(0),*/
                    ]),
            ]);
    }
}
