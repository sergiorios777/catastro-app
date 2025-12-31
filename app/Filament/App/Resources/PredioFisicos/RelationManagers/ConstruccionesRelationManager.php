<?php

namespace App\Filament\App\Resources\PredioFisicos\RelationManagers;

use App\Models\Depreciacion;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class ConstruccionesRelationManager extends RelationManager
{
    protected static string $relationship = 'construcciones';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components($this->getFormComponents());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nro_piso')
            ->columns([
                // 1. Identificación
                Tables\Columns\TextColumn::make('nro_piso')
                    ->label('Piso')
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(
                        fn($state, $record) =>
                        $state . ($record->seccion ? " ({$record->seccion})" : '')
                    ), // Muestra: "1 (A)" si hay sección

                // 2. Dimensiones
                Tables\Columns\TextColumn::make('area_construida')
                    ->label('Área')
                    ->suffix(' m²')
                    ->numeric(2)
                    ->sortable()
                    ->weight('bold'),

                // 3. Antigüedad y Material
                Tables\Columns\TextColumn::make('anio_construccion')
                    ->label('Antigüedad')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state . ' (' . (now()->year - $state) . ' años)'),

                Tables\Columns\TextColumn::make('material_estructural')
                    ->label('Material')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'concreto', 'ladrillo' => 'success',
                        'madera', 'adobe' => 'warning',
                        'drywall' => 'gray',
                        default => 'info',
                    }),

                // 4. Categorías Principales (Las que más impactan valor)
                Tables\Columns\TextColumn::make('muros_columnas')
                    ->label('Muros')
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('techos')
                    ->label('Techos')
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('puertas_ventanas')
                    ->label('Puert./Vent.')
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('estado_conservacion')
                    ->label('Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'muy_bueno' => 'success',
                        'bueno' => 'info',
                        'regular' => 'warning',
                        'malo' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst(str_replace('_', ' ', $state))),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar Piso/Nivel')
                    ->modalHeading('Registrar Nueva Construcción')
                    ->slideOver(), // Hace que el formulario salga del lado derecho (opcional, se ve moderno)
                //AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Actualizar Construcción')
                    ->form(function (Schema $schema) {
                        // Obtenemos el esquema original del formulario
                        // pero añadimos un campo especial al final
                        return $schema->components([
                            // 1. Renderizamos los campos normales de la construcción
                            Section::make('Datos de la Construcción')
                                ->schema([
                                    ...$this->getFormComponents(),
                                ])
                                ->columns(2)
                                ->columnSpanFull(),

                            // 2. Agregamos el control de decisión
                            Section::make('Tipo de Operación')
                                ->schema([
                                    Forms\Components\Radio::make('tipo_edicion')
                                        ->label('¿Qué tipo de cambio está realizando?')
                                        ->options([
                                            'correccion' => 'Corrección de error (No genera historial)',
                                            'actualizacion' => 'Nueva fiscalización / Cambio real (Genera historial)',
                                        ])
                                        ->default('correccion')
                                        ->formatStateUsing(fn($state) => $state ?? 'correccion')
                                        ->required()
                                        ->columnSpanFull(),
                                ])
                                ->secondary()
                                ->icon('heroicon-o-exclamation-triangle'),
                        ]);
                    })
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

    public function getFormComponents(): array
    {
        // Definimos la lógica de cálculo en una variable para reutilizarla
        $calcularDepreciacion = function (Get $get, Set $set) {
            // 1. Recopilar datos
            $material = $get('material_estructural');
            $uso = $get('uso_especifico');
            $anio = (int) $get('anio_construccion');
            $estado = $get('estado_conservacion'); // CORREGIDO: Usamos get, no state

            // Validación: Si falta algún dato, no calculamos
            if (!$material || !$uso || !$anio || !$estado) {
                return;
            }

            $antiguedad = now()->year - $anio;

            // 2. Buscar en la Matriz RNT
            // Asumo que tu modelo Depreciacion tiene este método estático
            $porcentajeOficial = Depreciacion::buscar($material, $uso, $estado, $antiguedad);

            // 3. Establecer el valor
            if ($porcentajeOficial !== null) {
                $set('porcentaje_depreciacion_manual', $porcentajeOficial);
            }
        };

        return [
            Section::make('Características Físicas')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('nro_piso')
                                ->label('Nro. Piso')
                                ->numeric()
                                ->default(1)
                                ->required(),

                            TextInput::make('seccion')
                                ->label('Sección / Bloque')
                                ->placeholder('Ej: A'),

                            TextInput::make('area_construida')
                                ->label('Área Construida (m2)')
                                ->numeric()
                                ->suffix('m²')
                                ->required(),

                            TextInput::make('area_comun')
                                ->label('Área Común (m2)')
                                ->numeric()
                                ->default(0)
                                ->suffix('m²'),
                        ]),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('anio_construccion')
                                ->label('Año Construcción')
                                ->live()
                                ->numeric()
                                ->minValue(1900)
                                ->maxValue(now()->year)
                                ->afterStateUpdated($calcularDepreciacion)
                                ->required(),

                            Select::make('uso_especifico')
                                ->label('Uso / Clasificación RNT')
                                ->options([
                                    'casa_habitacion' => 'Vivienda / Depto',
                                    'tienda_deposito' => 'Tiendas / Depósitos',
                                    'edificio_oficina' => 'Oficinas / Edificios',
                                    'industria_salud' => 'Industria / Salud / Cine',
                                    'otros' => 'Otro (Adobe/Madera)',
                                ])
                                ->live()
                                ->afterStateUpdated($calcularDepreciacion)
                                ->required(),

                            Select::make('material_estructural')
                                ->label('Material Predominante')
                                ->options([
                                    'concreto' => 'Concreto',
                                    'ladrillo' => 'Ladrillo',
                                    'adobe' => 'Adobe',
                                    'madera' => 'Madera',
                                    'drywall' => 'Drywall / Prefab.',
                                ])
                                ->live()
                                ->afterStateUpdated($calcularDepreciacion)
                                ->required(),

                            Select::make('estado_conservacion')
                                ->label('Estado Conservación')
                                ->options([
                                    'muy_bueno' => 'Muy Bueno',
                                    'bueno' => 'Bueno',
                                    'regular' => 'Regular',
                                    'malo' => 'Malo',
                                ])
                                ->live()
                                ->default('regular')
                                ->afterStateUpdated($calcularDepreciacion)
                                ->required(),

                            // CAMPO 1: VISUALIZACIÓN
                            Placeholder::make('calculo_depreciacion_preview')
                                ->label('Depreciación Calculada')
                                // Ahora el placeholder solo LEERÁ el valor, no lo escribirá
                                ->content(fn(Get $get) => $get('porcentaje_depreciacion_manual')
                                    ? $get('porcentaje_depreciacion_manual') . '%'
                                    : '-'),

                            // CAMPO 2: El valor final a aplicar (Editable)
                            TextInput::make('porcentaje_depreciacion_manual')
                                ->label('Depreciación a Aplicar')
                                ->helperText('Edite solo si el criterio técnico difiere del RNT.')
                                ->numeric()
                                ->suffix('%')
                                ->required(), // Es obligatorio porque el cálculo matemático lo necesita
                        ]),
                ]),

            Section::make('Categorías y Componentes')
                ->description('Seleccione la categoría (A-J) para cada componente estructural.')
                ->schema([
                    Grid::make(2) // 4 columnas para compactar
                        ->schema([
                            // Usamos Select simple para no saturar visualmente
                            $this->makeCategoriaSelect('muros_columnas', 'Muros y Columnas')->required(),
                            $this->makeCategoriaSelect('techos', 'Techos')->required(),
                            $this->makeCategoriaSelect('pisos', 'Pisos'),
                            $this->makeCategoriaSelect('puertas_ventanas', 'Puertas/Ventanas')->required(),
                            $this->makeCategoriaSelect('revestimientos', 'Revestimientos'),
                            $this->makeCategoriaSelect('banos', 'Baños'),
                            $this->makeCategoriaSelect('inst_electricas_sanitarias', 'Inst. Eléctricas'),
                        ]),
                ]),
        ];
    }

    // Función auxiliar para no repetir código 7 veces
    protected function makeCategoriaSelect(string $name, string $label)
    {
        return Select::make($name)
            ->label($label)
            ->options([
                'A' => 'A',
                'B' => 'B',
                'C' => 'C',
                'D' => 'D',
                'E' => 'E',
                'F' => 'F',
                'G' => 'G',
                'H' => 'H',
                'I' => 'I',
                'J' => 'J',
            ])
            ->searchable()
            ->preload();
    }
}
