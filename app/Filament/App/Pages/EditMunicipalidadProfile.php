<?php

namespace App\Filament\App\Pages;

use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Pages\Tenancy\EditTenantProfile;

class EditMunicipalidadProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Configuración Municipal';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Identidad Institucional')
                    ->description('Datos que aparecerán en recibos y documentos oficiales.')
                    ->icon('heroicon-o-building-library')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre de la Municipalidad')
                                    ->required(),

                                TextInput::make('ruc')
                                    ->label('R.U.C.')
                                    ->length(11)
                                    ->numeric()
                                    ->required(),
                            ]),

                        TextInput::make('slogan')
                            ->label('Slogan de Gestión')
                            ->placeholder('Ej: "Gestión con honestidad 2023-2026"')
                            ->columnSpanFull(),

                        TextInput::make('direccion_fiscal')
                            ->label('Dirección Fiscal')
                            ->placeholder('Av. Principal 123, Plaza de Armas')
                            ->columnSpanFull(),

                        TextInput::make('web')
                            ->label('Página Web')
                            ->url()
                            ->prefix('https://'),

                        FileUpload::make('logo')
                            ->label('Escudo / Logo')
                            ->image()
                            ->disk('public')
                            ->directory('logos-municipales') // Carpeta en storage/app/public
                            ->avatar() // Opcional: para que se vea redondo
                            ->imageEditor(),
                    ]),

                Section::make('Ubicación Geográfica')
                    ->description('Estos datos sobrescriben la configuración inicial del sistema.')
                    ->icon('heroicon-o-map')
                    ->columns(3)
                    ->collapsed()
                    ->schema([
                        TextInput::make('departamento')
                            ->label('Departamento'),
                        TextInput::make('provincia')
                            ->label('Provincia'),
                        TextInput::make('distrito')
                            ->label('Distrito'),
                        TextInput::make('ubigeo')
                            ->label('Código UBIGEO')
                            ->length(6)
                            ->numeric(),
                    ]),
            ]);
    }
}
