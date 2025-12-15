<?php

namespace App\Filament\Resources\Tenants\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;


class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalles de la Municipalidad')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del Municipio')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true) // Se activa al salir del campo
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                // Genera el slug automáticamente: "Muni Loreto" -> "muni-loreto"
                                $set('slug', Str::slug($state));
                            }),

                        TextInput::make('slug')
                            ->label('Identificador (Subdominio)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Usado para la URL: slug.tuapp.com'),

                        Select::make('status')
                            ->options([
                                'active' => 'Activo',
                                'suspended' => 'Suspendido',
                            ])
                            ->default('active')
                            ->required(),

                        Toggle::make('subscription_active')
                            ->label('Suscripción Activa')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger'),
                    ]),
            ]);
    }
}
