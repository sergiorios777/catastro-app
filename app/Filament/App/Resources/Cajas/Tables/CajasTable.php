<?php

namespace App\Filament\App\Resources\Cajas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn; // O TextColumn con badge() en v3
use Filament\Forms\Components\TextInput as FormTextInput; // Alias para evitar conflicto
use Filament\Notifications\Notification;

class CajasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cajero.name')
                    ->label('Cajero')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('fecha_apertura')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'abierta' => 'success',
                        'cerrada' => 'danger',
                        'arqueada' => 'warning',
                    }),

                TextColumn::make('monto_apertura')
                    ->money('PEN')
                    ->label('Inicio'),

                TextColumn::make('total_recaudado')
                    ->money('PEN')
                    ->label('Recaudado')
                    ->weight('bold'),

                // Columna virtual para ver cuánto debería haber en total
                TextColumn::make('total_esperado')
                    ->label('Total en Caja')
                    ->money('PEN')
                    ->state(fn($record) => $record->monto_apertura + $record->total_recaudado),

                TextColumn::make('fecha_cierre')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('---'),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                //EditAction::make(),
                // Acción para CERRAR CAJA
                Action::make('cerrar_caja')
                    ->label('Cerrar Caja')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cierre de Caja')
                    ->modalDescription('Ingrese el monto total de dinero físico que tiene en su poder.')
                    // Solo visible si está abierta y soy el dueño (o admin)
                    ->visible(fn($record) => $record->estado === 'abierta' && $record->user_id === auth()->id())
                    ->form([
                        FormTextInput::make('monto_en_mano')
                            ->label('Dinero Físico Contado')
                            ->prefix('S/.')
                            ->numeric()
                            ->required(),
                        FormTextInput::make('observaciones_cierre')
                            ->label('Observaciones')
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'fecha_cierre' => now(),
                            'monto_cierre' => $data['monto_en_mano'],
                            'observaciones' => $record->observaciones . "\nCierre: " . ($data['observaciones_cierre'] ?? ''),
                            'estado' => 'cerrada',
                        ]);

                        // OJO: Filament Action nativa no abre pestañas nuevas fácilmente desde el callback PHP.
                        // Lo más sencillo es mostrar una notificación con un botón (Action) dentro.
            
                        Notification::make()
                            ->title('Caja Cerrada')
                            ->success()
                            ->actions([
                                Action::make('imprimir')
                                    ->label('Imprimir Arqueo')
                                    ->button()
                                    ->url(route('imprimir.arqueo', ['caja' => $record->id]))
                                    ->openUrlInNewTab(),
                            ])
                            ->send();
                    }),
                // NUEVA ACCIÓN: Imprimir Arqueo
                // GRUPO DE REPORTES (Visible si NO está abierta)
                ActionGroup::make([
                    Action::make('imprimir_arqueo_a4')
                        ->label('Reporte A4')
                        ->icon('heroicon-o-document-text')
                        ->url(fn($record) => route('imprimir.arqueo', ['caja' => $record->id]))
                        ->openUrlInNewTab(),

                    Action::make('imprimir_arqueo_ticket')
                        ->label('Ticket 80mm')
                        ->icon('heroicon-o-receipt-percent')
                        ->url(fn($record) => route('imprimir.arqueo_ticket', ['caja' => $record->id]))
                        ->openUrlInNewTab(),
                ])
                    ->label('Reportes')
                    ->icon('heroicon-m-printer')
                    ->color('gray')
                    ->visible(fn($record) => $record->estado !== 'abierta'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
