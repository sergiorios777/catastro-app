<?php

namespace App\Filament\Resources\ReglaDescuentoTributos\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Str;

class ReglaDescuentoTributoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificación de la Norma')
                    ->description('Defina el código interno y el nombre público del beneficio.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('nombre')
                                ->label('Nombre del Beneficio')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true) // Generar código al salir del campo
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                    // Auto-generar código si está vacío
                                    if (!$get('codigo') && filled($state)) {
                                        $slug = Str::slug($state, '_');
                                        $set('codigo', strtoupper($slug));
                                    }
                                }),

                            TextInput::make('codigo')
                                ->label('Código Interno (Slug)')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(50)
                                ->helperText('Ej: PEN_50UIT. Debe ser único.'),
                        ]),

                        Textarea::make('base_legal')
                            ->label('Base Legal')
                            ->rows(2)
                            ->required()
                            ->placeholder('Ej: Art. 19 de la Ley de Tributación Municipal'),
                    ]),

                Section::make('Lógica de Cálculo')
                    ->description('Determine cómo afecta esta regla al algoritmo de impuestos.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('tipo_tributo')
                                ->options([
                                    'predial' => 'Impuesto Predial',
                                    'alcabala' => 'Impuesto de Alcabala',
                                    'arbitrios' => 'Arbitrios Municipales',
                                ])
                                ->required()
                                ->default('predial'),

                            Select::make('tipo_beneficio')
                                ->label('Tipo de Beneficio')
                                ->options([
                                    'deduccion' => 'Deducción (Resta valor)',
                                    'exoneracion' => 'Exoneración (Descuento %)',
                                    'inafectacion' => 'Inafectación (No paga)',
                                ])
                                ->required()
                                ->live() // ¡IMPORTANTE! Hace el formulario reactivo
                                ->afterStateUpdated(function (Set $set, ?string $state) {
                                    // Auto-seleccionar la aplicación lógica sugerida
                                    if ($state === 'deduccion') {
                                        $set('aplicacion_sobre', 'base_imponible');
                                    } elseif ($state === 'exoneracion' || $state === 'inafectacion') {
                                        $set('aplicacion_sobre', 'impuesto_calculado');
                                    }
                                }),
                        ]),

                        Radio::make('aplicacion_sobre')
                            ->label('¿Sobre qué se aplica?')
                            ->options([
                                'base_imponible' => 'Sobre la Base Imponible (Antes del cálculo)',
                                'impuesto_calculado' => 'Sobre el Impuesto (Después del cálculo)',
                            ])
                            ->required()
                            ->inline(),

                        // --- CAMPOS CONDICIONALES ---

                        // Caso A: Deducción (UITs)
                        TextInput::make('valor_uit_deducidos')
                            ->label('Cantidad de UIT a deducir')
                            ->numeric()
                            ->suffix('UIT')
                            ->visible(fn(Get $get) => $get('tipo_beneficio') === 'deduccion')
                            ->required(fn(Get $get) => $get('tipo_beneficio') === 'deduccion'),

                        // Caso B: Exoneración/Inafectación (%)
                        TextInput::make('porcentaje_descuento')
                            ->label('Porcentaje de Descuento')
                            ->numeric()
                            ->suffix('%')
                            ->default(100)
                            ->visible(fn(Get $get) => in_array($get('tipo_beneficio'), ['exoneracion', 'inafectacion']))
                            ->required(fn(Get $get) => in_array($get('tipo_beneficio'), ['exoneracion', 'inafectacion'])),
                    ]),

                Section::make('Vigencia y Estado')
                    ->schema([
                        Grid::make(3)->schema([
                            DatePicker::make('valid_from')
                                ->label('Válido Desde')
                                ->default(now()->startOfYear())
                                ->required(),

                            DatePicker::make('valid_to')
                                ->label('Válido Hasta')
                                ->helperText('Dejar vacío si es indefinido'),

                            Toggle::make('is_active')
                                ->label('Regla Activa')
                                ->default(true)
                                ->onColor('success')
                                ->offColor('danger')
                                ->inline(false),
                        ]),
                    ]),
            ]);
    }
}
