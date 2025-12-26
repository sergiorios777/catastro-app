<?php

namespace App\Filament\App\Resources\PredioFisicos\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
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
            ->columns(2)
            ->components([
                Group::make()
                    ->columnspan(1)
                    ->schema([
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
                                    ->label('Tipo de Predio')
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

                                TextInput::make('area_verificada')
                                    ->label('Área Verificada (m²)')
                                    ->numeric()
                                    ->prefix('m²')
                                    ->minValue(0)
                                    ->statePath('info_complementaria.area_verificada'),

                                Select::make('clasificacion_predio')
                                    ->label('Clasificación del Predio')
                                    ->options([
                                        'casa_habitacion' => 'Casa habitación',
                                        'tienda_deposito' => 'Tienda/depósito/almacén',
                                        'predio_en_edificio' => 'Predio en edificio',
                                        'otro' => 'Otro (especificar)',
                                    ])
                                    ->default('casa_habitacion')
                                    ->required()
                                    ->live()
                                    ->statePath('info_complementaria.clasificacion_predio'),

                                Select::make('clasificacion_predio_otro')
                                    ->label('Especifique otro')
                                    ->options([
                                        'clinica' => 'Clínica',
                                        'hospital' => 'Hospital',
                                        'cine_teatro' => 'Cine/Teatro',
                                        'industria' => 'Industria',
                                        'taller' => 'Taller',
                                        'iglesia_templo' => 'Iglesia/Templo',
                                        'centro_ensenanza' => 'Centro de enseñanza',
                                        'servicio_comida' => 'Servicio de comida',
                                        'parque' => 'Parque',
                                        'cementerio' => 'Cementerio',
                                        'sub_estacion' => 'Sub estación',
                                        'banco' => 'Banco/Financiera',
                                        'terminal_transporte' => 'Terminal de transporte',
                                        'mercado' => 'Mercado',
                                        'club_social' => 'Club social',
                                        'club_esparcimiento' => 'Club de esparcimiento',
                                        'playa_estacionamiento' => 'Playa de estacionamiento',
                                        'otros' => 'Otros',
                                    ])
                                    ->statePath('info_complementaria.clasificacion_predio_otro')
                                    ->visible(fn(Get $get) => $get('info_complementaria.clasificacion_predio') === 'otro'),

                                Select::make('ubicacion_predio')
                                    ->label('Ubicación del Predio')
                                    ->options([
                                        'galeria' => 'Galería',
                                        'mercado' => 'Mercado',
                                        'campo_ferial' => 'Campo ferial',
                                        'centro_comercial' => 'Centro comercial',
                                        'quinta' => 'Quinta',
                                        'callejon' => 'Callejón',
                                        'predio_independiente' => 'Predio independiente',
                                        'solar' => 'Solar',
                                        'corralon' => 'Corralón',
                                        'azotea' => 'Azotea',
                                        'aires' => 'Aires',
                                        'predio_en_edificio' => 'Predio en edificio',
                                        'otros' => 'Otros (especificar)',
                                    ])
                                    ->default('predio_independiente')
                                    ->required()
                                    ->live()
                                    ->statePath('info_complementaria.ubicacion_predio'),

                                TextInput::make('ubicacion_predio_otro')
                                    ->label('Especificar otra ubic.')
                                    ->statePath('info_complementaria.ubicacion_predio_otro')
                                    ->visible(fn(Get $get) => $get('info_complementaria.ubicacion_predio') === 'otros'),

                            ]),

                        // SECCIÓN 5: Linderos
                        Section::make('Linderos')
                            ->statePath('info_complementaria.linderos')
                            ->columns(4)
                            ->schema([
                                TextInput::make('frente_medida')
                                    ->label('Frente (m)')
                                    ->numeric()
                                    ->prefix('m')
                                    ->minValue(0),

                                TextInput::make('frente_colindancia')
                                    ->label('Colindancia frente')
                                    ->columnSpan(3)
                                    ->placeholder('p.e.: Lote 0025, Emilio Lavajos'),

                                TextInput::make('derecha_medida')
                                    ->label('Derecha (m)')
                                    ->numeric()
                                    ->prefix('m')
                                    ->minValue(0),

                                TextInput::make('derecha_colindancia')
                                    ->label('Colindancia derecha')
                                    ->columnSpan(3)
                                    ->placeholder('p.e.: Calle Amazonas, Plaza José Galvez'),

                                TextInput::make('izquierda_medida')
                                    ->label('Izquierda (m)')
                                    ->numeric()
                                    ->prefix('m')
                                    ->minValue(0),

                                TextInput::make('izquierda_colindancia')
                                    ->label('Colindancia izquierda')
                                    ->columnSpan(3)
                                    ->placeholder('p.e.: Lote 0025, Emilio Lavajos'),

                                TextInput::make('fondo_medida')
                                    ->label('Fondo (m)')
                                    ->numeric()
                                    ->prefix('m')
                                    ->minValue(0),

                                TextInput::make('fondo_colindancia')
                                    ->label('Colindancia fondo')
                                    ->columnSpan(3),

                            ]),


                    ]),

                Group::make()
                    ->columnspan(1)
                    ->schema([
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

                                // Grupo de Checkboxes para servicios extra
                                Section::make('Servicios extras')
                                    ->columnSpan(1)
                                    ->compact()
                                    ->columns(4)
                                    ->statePath('info_complementaria.servicios_extra')
                                    ->schema([
                                        Toggle::make('tiene_telefono')->label('Teléfono')->inline(false),
                                        Toggle::make('tiene_gas')->label('Gas')->inline(false),
                                        Toggle::make('tiene_intenet')->label('Internet')->inline(false),
                                        Toggle::make('tiene_cable')->label('Cable')->inline(false),
                                    ]),

                            ]),

                    ])

            ]);
    }
}
