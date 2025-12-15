<?php

namespace App\Filament\App\Resources\Personas\Schemas;

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
                            ->required()
                            ->maxLength(15)
                            ->unique(ignoreRecord: true),

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
                            ->maxLength(255),
                    ]),
            ]);
    }
}
