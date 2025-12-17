<?php

namespace App\Filament\App\Resources\PredioFisicos\RelationManagers;

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
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ConstruccionesRelationManager extends RelationManager
{
    protected static string $relationship = 'construcciones';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Características Físicas')
                    ->schema([
                        Grid::make(3)
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
                                    ->label('Área (m2)')
                                    ->numeric()
                                    ->suffix('m²')
                                    ->required(),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('anio_construccion')
                                    ->label('Año Construcción')
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue(now()->year)
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
                                    ->required(),

                                Select::make('estado_conservacion')
                                    ->label('Estado Conservación')
                                    ->options([
                                        'muy_bueno' => 'Muy Bueno',
                                        'bueno' => 'Bueno',
                                        'regular' => 'Regular',
                                        'malo' => 'Malo',
                                    ])
                                    ->default('regular')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Categorías y Componentes')
                    ->description('Seleccione la categoría (A-J) para cada componente estructural.')
                    ->schema([
                        Grid::make(4) // 4 columnas para compactar
                            ->schema([
                                // Usamos Select simple para no saturar visualmente
                                $this->makeCategoriaSelect('muros_columnas', 'Muros y Columnas'),
                                $this->makeCategoriaSelect('techos', 'Techos'),
                                $this->makeCategoriaSelect('pisos', 'Pisos'),
                                $this->makeCategoriaSelect('puertas_ventanas', 'Puertas/Ventanas'),
                                $this->makeCategoriaSelect('revestimientos', 'Revestimientos'),
                                $this->makeCategoriaSelect('banos', 'Baños'),
                                $this->makeCategoriaSelect('inst_electricas_sanitarias', 'Inst. Eléctricas'),
                            ]),
                    ]),
            ]);
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
