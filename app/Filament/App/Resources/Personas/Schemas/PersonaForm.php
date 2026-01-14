<?php

namespace App\Filament\App\Resources\Personas\Schemas;

use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PersonaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificación')
                    ->columns(2)
                    ->schema([
                        // 1. Selector de Tipo
                        Select::make('tipo_persona')
                            ->options([
                                'natural' => 'Persona Natural',
                                'juridica' => 'Persona Jurídica',
                            ])
                            ->required()
                            ->default('natural')
                            ->live(), // Reactividad en tiempo real

                        // 2. Selector de Documento
                        Select::make('tipo_documento')
                            ->options([
                                'DNI' => 'DNI',
                                'RUC' => 'RUC',
                                'CE' => 'Carnet Extranjería',
                                'PAS' => 'Pasaporte',
                            ])
                            ->required(),

                        TextInput::make('numero_documento')
                            ->label('Número Documento')
                            ->required()
                            ->maxLength(15)
                            // Regla de Unicidad con Scope (Filtro) por Tenant
                            ->unique(
                                table: 'personas',
                                column: 'numero_documento',
                                ignoreRecord: true, // Importante: Permite editar el mismo registro sin error
                                modifyRuleUsing: function (Unique $rule) {
                                    // Aquí le decimos: "Solo busca duplicados donde el tenant_id sea el mío"
                                    return $rule->where('tenant_id', auth()->user()->tenant_id);
                                }
                            ),

                        // 3. Campos Condicionales
                        TextInput::make('nombres')
                            ->required(fn(Get $get) => $get('tipo_persona') === 'natural')
                            ->hidden(fn(Get $get) => $get('tipo_persona') === 'juridica'),

                        TextInput::make('apellidos')
                            ->required(fn(Get $get) => $get('tipo_persona') === 'natural')
                            ->hidden(fn(Get $get) => $get('tipo_persona') === 'juridica'),

                        TextInput::make('razon_social')
                            ->label('Razón Social')
                            ->columnSpanFull()
                            ->required(fn(Get $get) => $get('tipo_persona') === 'juridica')
                            ->hidden(fn(Get $get) => $get('tipo_persona') !== 'juridica'),
                    ]),

                Section::make('Contacto')
                    ->collapsible()
                    ->columns(3)
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('telefono')
                            ->tel()
                            ->maxLength(20),
                        TextInput::make('direccion')
                            ->columnSpanFull()
                            ->maxLength(255)
                            ->required(),
                        TextInput::make('departamento')
                            ->maxLength(80)
                            ->statePath('ubicacion_geografica.departamento'),
                        TextInput::make('provincia')
                            ->maxLength(80)
                            ->statePath('ubicacion_geografica.provincia'),
                        TextInput::make('distrito')
                            ->maxLength(80)
                            ->statePath('ubicacion_geografica.distrito'),
                        TextInput::make('cuenca')
                            ->maxLength(80)
                            ->statePath('ubicacion_geografica.cuenca'),
                        TextInput::make('localidad')
                            ->maxLength(80)
                            ->statePath('ubicacion_geografica.localidad'),
                    ]),
            ]);
    }
}
