<?php

namespace App\Filament\App\Resources\DeterminacionPredials\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Caja;
use App\Models\Pago;
use App\Models\DeterminacionPredial;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Services\TesoreriaService;

class DeterminacionPredialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Columnas informativas
                Tables\Columns\TextColumn::make('persona.numero_documento')
                    ->label('DNI/RUC')
                    ->searchable(),
                Tables\Columns\TextColumn::make('persona.nombrecompleto') // Asumiendo accessor o relación
                    ->label('Contribuyente')
                    ->searchable(['nombres', 'apellidos', 'razon_social']),
                Tables\Columns\TextColumn::make('anioFiscal.anio')
                    ->label('Año'),
                Tables\Columns\TextColumn::make('base_imponible')
                    ->money('PEN')
                    ->label('Base Imponible'),
                Tables\Columns\TextColumn::make('impuesto_calculado')
                    ->money('PEN')
                    ->label('Impuesto Predial'),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pendiente' => 'danger',
                        'pagado' => 'success',
                        'anulado' => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                // --- BOTÓN PAGAR ---
                Action::make('pagar_deuda')
                    ->label('Cobrar')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    // Solo visible si está pendiente
                    ->visible(fn(DeterminacionPredial $record) => $record->estado === 'pendiente')

                    // Validación Previa: ¿Hay caja abierta?
                    ->disabled(fn() => !Caja::abierta(auth()->id())->exists())
                    ->tooltip(fn() => !Caja::abierta(auth()->id())->exists() ? 'Debes abrir caja primero' : 'Registrar pago')

                    ->modalHeading('Registrar Pago de Impuesto')
                    ->modalDescription(fn($record) => "Monto a cobrar: S/. " . number_format($record->impuesto_calculado, 2))

                    ->form([
                        Select::make('metodo_pago')
                            ->label('Forma de Pago')
                            ->options([
                                'efectivo' => 'Efectivo',
                                'transferencia' => 'Transferencia Bancaria',
                                'yape' => 'Yape / Plin',
                                'tarjeta' => 'Tarjeta de Crédito/Débito',
                            ])
                            ->default('efectivo')
                            ->required()
                            ->reactive(), // Para mostrar/ocultar el campo de referencia

                        TextInput::make('referencia_pago')
                            ->label('Nro. Operación / Voucher')
                            ->placeholder('Ej: 123456')
                            // Solo requerido si NO es efectivo
                            ->hidden(fn($get) => $get('metodo_pago') === 'efectivo')
                            ->required(fn($get) => $get('metodo_pago') !== 'efectivo'),
                    ])
                    ->action(function (DeterminacionPredial $record, array $data) {
                        try {
                            // Inyectamos el servicio manualmente
                            $servicio = new TesoreriaService();
                            $pago = $servicio->procesarPago($record, $data);

                            Notification::make()
                                ->title('Pago Registrado Exitosamente')
                                ->body("Recibo N° {$pago->serie}-{$pago->numero}")
                                ->success()
                                ->send();

                            // Opcional: Aquí podríamos redirigir a imprimir el recibo
            
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error en el proceso')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // Botón para ver el recibo (Solo si ya está pagado)
                ActionGroup::make([
                    Action::make('imprimir_a4')
                        ->label('Formato A4')
                        ->icon('heroicon-o-document')
                        ->url(fn(DeterminacionPredial $record) => route('imprimir.recibo', ['pago' => $record->pago->id]))
                        ->openUrlInNewTab(), // <--- ESTO ABRE PESTAÑA NUEVA

                    Action::make('imprimir_ticket')
                        ->label('Ticket 80mm')
                        ->icon('heroicon-o-receipt-percent') // O el icono que prefieras
                        ->url(fn(DeterminacionPredial $record) => route('imprimir.ticket', ['pago' => $record->pago->id]))
                        ->openUrlInNewTab(), // <--- ESTO TAMBIÉN
                ])
                    ->label('Comprobante')
                    ->icon('heroicon-m-printer')
                    ->color('gray')
                    ->visible(fn(DeterminacionPredial $record) => $record->estado === 'pagado'),

                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
