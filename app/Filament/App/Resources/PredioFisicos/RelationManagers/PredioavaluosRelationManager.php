<?php

namespace App\Filament\App\Resources\PredioFisicos\RelationManagers;

use Illuminate\Database\Eloquent\Model;
use Filament\Support\Enums\Width;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ActionGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Radio;

class PredioavaluosRelationManager extends RelationManager
{
    protected static string $relationship = 'predioFisicoAvaluos';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('predio_fisico_id')
            ->columns([
                /*TextColumn::make('predio_fisico_id')
                    ->label('Predio Físico')
                    ->sortable()
                    ->searchable(),*/
                // Características físicas
                TextColumn::make('area_terreno')
                    ->label('Área Terreno')
                    ->numeric()
                    ->suffix(' m²')
                    ->sortable(),
                /*TextColumn::make('zona')
                    ->label('Zona'),*/
                // Características para Aranceles
                TextColumn::make('tipo_calzada')
                    ->visible(fn() => $this->getTipoPredio() === 'urbano')
                    ->label('Tipo Calzada')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'tierra' => 'Tierra',
                        'afirmado' => 'Afirmado',
                        'empedrado' => 'Empedrado',
                        'asfalto' => 'Asfalto',
                        'concreto' => 'Concreto',
                        default => $state,
                    }),
                TextColumn::make('ancho_via')
                    ->visible(fn() => $this->getTipoPredio() === 'urbano')
                    ->label('Ancho Via')
                    ->numeric()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'menos_6' => '< 6m',
                        'entre_6_8' => '6m - 8m',
                        'mas_8' => '> 8m',
                        default => $state,
                    }),
                // Predio tipo rústico
                TextColumn::make('grupo_tierras')
                    ->visible(fn() => $this->getTipoPredio() === 'rustico')
                    ->label('Grupo Tierras')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'A' => 'Tierras Aptas para Cultivo (A)',
                        'C' => 'Tierras para Cultivo Permanente (C)',
                        'P' => 'Pastos (P)',
                        'X' => 'Eriazas (X)',
                        default => $state,
                    }),
                TextColumn::make('distancia')
                    ->visible(fn() => $this->getTipoPredio() === 'rustico')
                    ->label('Distancia')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'hasta_1km' => 'Hasta 1.00 km',
                        'de_1_2km' => 'Más de 1.00 hasta 2.00 km',
                        'de_2_3km' => 'Más de 2.00 hasta 3.00 km',
                        'mas_3km' => 'Más de 3.00 km',
                        default => $state,
                    }),
                TextColumn::make('calidad_agrologica')
                    ->visible(fn() => $this->getTipoPredio() === 'rustico')
                    ->label('Calidad Agrologica')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'alta' => 'Alta',
                        'media' => 'Media',
                        'baja' => 'Baja',
                        default => $state,
                    }),
                IconColumn::make('tiene_agua')
                    ->label('Agua')
                    ->boolean(),
                IconColumn::make('tiene_desague')
                    ->label('Desague')
                    ->boolean(),
                IconColumn::make('tiene_luz')
                    ->label('Luz')
                    ->boolean(),

                // Validación
                TextColumn::make('valid_from')
                    ->label('Valid From')
                    ->date(),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                TextColumn::make('version')
                    ->label('Version')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar Info. Predio')
                    ->modalHeading('Agregar información del predio')
                    ->modalIcon('heroicon-o-document-text')
                    ->modalWidth(Width::FiveExtraLarge),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Editar Info. Predio')
                        ->modalHeading('Editar información del predio')
                        ->modalIcon('heroicon-o-document-text')
                        ->modalWidth(Width::FiveExtraLarge)
                        ->using(function (Model $record, array $data): Model {
                            // Extraemos la decisión del usuario
                            $tipoEdicion = $data['tipo_edicion'] ?? 'actualizacion';

                            // Limpiamos el array de datos para que no intente guardar el campo 'tipo_edicion' en la BD
                            unset($data['tipo_edicion']);

                            if ($tipoEdicion === 'correccion') {
                                // OPCIÓN A: Solo corregir (Update normal)
                                $record->update($data);
                                return $record;
                            } else {
                                // OPCIÓN B: Generar nueva versión (La función del Trait que creamos antes)
                                return $record->createNewVersion($data);
                            }
                        })
                        ->successNotificationTitle('Registro actualizado correctamente'),
                    DeleteAction::make(),
                ]),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components($this->getComponents());
    }

    public function getComponents(): array
    {
        return [
            Group::make()
                ->columnSpan(1)
                ->schema([
                    // Sección 1: Características Físicas
                    Section::make('Características Físicas')
                        ->columns(2)
                        ->schema([
                            TextInput::make('area_terreno')
                                ->label('Área de Terreno (m²)')
                                ->numeric()
                                ->prefix('m²')
                                ->required()
                                ->minValue(0),
                            /*Select::make('tipo_predio')
                                ->label('Tipo de Predio')
                                ->options(['urbano' => 'Urbano', 'rustico' => 'Rústico'])
                                ->default('urbano')
                                ->required()
                                ->live(),
                            Select::make('estado')
                                ->options(['activo' => 'Activo', 'fusionado' => 'Fusionado (Histórico)', 'dividido' => 'Dividido (Histórico)', 'inactivo' => 'Inactivo'])
                                ->default('activo')
                                ->required()
                                ->native(false), */
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

                    // Sección 2: Linderos
                    Section::make('Linderos')
                        ->statePath('info_complementaria.linderos')
                        ->columns(4)
                        ->schema([
                            TextInput::make('frente_medida')->label('Frente (m)')->numeric(),
                            TextInput::make('frente_colindancia')->label('Colindancia')->columnSpan(3),
                            TextInput::make('derecha_medida')->label('Derecha (m)')->numeric(),
                            TextInput::make('derecha_colindancia')->label('Colindancia')->columnSpan(3),
                            TextInput::make('izquierda_medida')->label('Izquierda (m)')->numeric(),
                            TextInput::make('izquierda_colindancia')->label('Colindancia')->columnSpan(3),
                            TextInput::make('fondo_medida')->label('Fondo (m)')->numeric(),
                            TextInput::make('fondo_colindancia')->label('Colindancia')->columnSpan(3),
                        ]),
                ]),

            Group::make()
                ->columnSpan(1)
                ->schema([
                    // SECCIÓN 4: Arancel
                    Section::make('Características para Arancel')
                        ->schema([
                            Grid::make(2)->visible(fn() => $this->getTipoPredio() === 'urbano')->schema([
                                Select::make('tipo_calzada')
                                    ->options([
                                        'tierra' => 'Tierra',
                                        'afirmado' => 'Afirmado',
                                        'empedrado' => 'Empedrado',
                                        'asfalto' => 'Asfalto',
                                        'concreto' => 'Concreto',
                                    ])
                                    ->required(),
                                Select::make('ancho_via')
                                    ->options([
                                        'menos_6' => 'Menos de 6.00 ml',
                                        'entre_6_8' => 'Entre 6.00 y 8.00 ml',
                                        'mas_8' => 'Más de 8.00 ml',
                                    ])
                                    ->required(),
                            ]),
                            Grid::make(3)->visible(fn() => $this->getTipoPredio() === 'rustico')->schema([
                                Select::make('grupo_tierras')
                                    ->label('Grupo de Tierras')
                                    ->options([
                                        'A' => 'Tierras Aptas para Cultivo (A)',
                                        'C' => 'Tierras para Cultivo Permanente (C)',
                                        'P' => 'Pastos (P)',
                                        'X' => 'Eriazas (X)',
                                    ])
                                    ->required(),
                                Select::make('distancia')
                                    ->label('Distancia a Vías/Ríos')
                                    ->options([
                                        'hasta_1km' => 'Hasta 1.00 km',
                                        'de_1_2km' => 'Más de 1.00 hasta 2.00 km',
                                        'de_2_3km' => 'Más de 2.00 hasta 3.00 km',
                                        'mas_3km' => 'Más de 3.00 km',
                                    ])
                                    // OJO: Según tu schema, distancia puede ser NULL en algunos grupos de tierras.
                                    // Aquí lo dejo required para simplificar, pero podrías condicionarlo.
                                    ->required(),
                                Select::make('calidad_agrologica')
                                    ->options(['alta' => 'Alta', 'media' => 'Media', 'baja' => 'Baja'])->required(),
                            ]),
                            Section::make('Servicios')->columnSpan(1)->compact()->columns(3)->schema([
                                Toggle::make('tiene_luz')->label('Luz'),
                                Toggle::make('tiene_agua')->label('Agua'),
                                Toggle::make('tiene_desague')->label('Desagüe'),
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
                                    Toggle::make('tiene_internet')->label('Internet')->inline(false),
                                    Toggle::make('tiene_cable')->label('Cable')->inline(false),
                                ]),
                        ]),

                    // SECCIÓN 6: Tipo de edicición (actualización o nueva versión)
                    Section::make('Control de Cambios')
                        ->schema([
                            Radio::make('tipo_edicion')
                                ->label('Tipo de Operación')
                                ->options([
                                    'correccion' => 'Corrección de error (No genera historial)',
                                    'actualizacion' => 'Nueva fiscalización (Genera historial)',
                                ])
                                ->default('correccion')
                                ->formatStateUsing(fn($state) => $state ?? 'correccion')
                                ->required()
                                ->columnSpanFull(),
                        ])
                        ->secondary()
                        ->icon('heroicon-o-exclamation-triangle')
                        // MAGIA: Solo visible si estamos EDITANDO un registro existente
                        ->visible(fn($record) => $record !== null),
                ]),
        ];
    }

    // Obtiene el tipo de predio de la tabla padre (predios_fisicos)
    private function getTipoPredio(): string
    {
        return $this->getOwnerRecord()?->tipo_predio;
    }
}
