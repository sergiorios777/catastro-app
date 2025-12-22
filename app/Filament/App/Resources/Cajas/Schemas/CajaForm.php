<?php

namespace App\Filament\App\Resources\Cajas\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;

class CajaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalle de Sesión')
                    ->columns(2)
                    ->schema([
                        // Solo lectura porque el sistema lo llena
                        TextInput::make('cajero_nombre')
                            ->label('Cajero Responsable')
                            ->formatStateUsing(fn($record) => $record?->cajero->name ?? auth()->user()->name)
                            ->disabled(),

                        TextInput::make('estado')
                            ->label('Estado Actual')
                            ->disabled(),

                        DateTimePicker::make('fecha_apertura')
                            ->label('Apertura')
                            ->default(now())
                            ->required()
                            ->disabled(), // No se debe editar la fecha manualmente

                        DateTimePicker::make('fecha_cierre')
                            ->label('Cierre')
                            ->disabled(),
                    ]),

                Section::make('Arqueo de Dinero')
                    ->columns(3)
                    ->schema([
                        TextInput::make('monto_apertura')
                            ->label('Monto Inicial (Sencillo)')
                            ->prefix('S/.')
                            ->numeric()
                            ->required()
                            // Solo editable si estamos creando una nueva caja
                            ->disabled(fn(string $operation) => $operation !== 'create'),

                        TextInput::make('total_recaudado')
                            ->label('Ingresos del Sistema')
                            ->prefix('S/.')
                            ->numeric()
                            ->default(0)
                            ->disabled(), // Esto se llena solo con los pagos

                        TextInput::make('monto_cierre')
                            ->label('Dinero en Mano (Al cierre)')
                            ->prefix('S/.')
                            ->numeric()
                            ->disabled(), // Se llena mediante la acción de cerrar
                    ]),

                Textarea::make('observaciones')
                    ->columnSpanFull(),
            ]);
    }
}
