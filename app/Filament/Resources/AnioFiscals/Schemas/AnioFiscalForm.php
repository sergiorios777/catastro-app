<?php

namespace App\Filament\Resources\AnioFiscals\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AnioFiscalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Configuración General')
                    ->schema([
                        TextInput::make('anio')
                            ->label('Año Fiscal')
                            ->numeric()
                            ->minValue(2000)
                            ->maxValue(2100)
                            ->default(now()->year + 1)
                            ->required(),

                        Toggle::make('activo')
                            ->label('Año Activo para Operaciones')
                            ->helperText('Activar este año para cálculos actuales.'),
                    ])->columns(2),

                Section::make('Parámetros Económicos')
                    ->schema([
                        TextInput::make('valor_uit')
                            ->label('Valor UIT (S/.)')
                            ->prefix('S/.')
                            ->numeric()
                            ->required(),

                        TextInput::make('factor_oficializacion')
                            ->label('Factor Oficialización')
                            ->helperText('Coeficiente para obras complementarias (Ej: 0.68)')
                            ->numeric()
                            ->default(0.68)
                            ->step(0.01)
                            ->required(),

                        TextInput::make('costo_emision')
                            ->label('Costo de Emisión')
                            ->helperText('Derecho de emisión mecanizada')
                            ->prefix('S/.')
                            ->numeric()
                            ->default(0),

                        TextInput::make('tasa_ipm')
                            ->label('Tasa IPM (%)')
                            ->helperText('Impuesto de Promoción Municipal')
                            ->suffix('%')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),
            ]);
    }
}
