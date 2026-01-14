<?php

namespace App\Filament\App\Resources\PredioFisicos\Pages;

use App\Filament\App\Resources\PredioFisicos\PredioFisicoResource;
use App\Models\PredioFisico;
use App\Services\CalculoAutoavaluoService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Model;

class EditPredioFisico extends EditRecord
{
    protected static string $resource = PredioFisicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // BOTÓN MÁGICO DE CÁLCULO 🧮
            /*Action::make('calcular_autoavaluo')
                ->label('Calcular Autoavalúo')
                ->icon('heroicon-o-calculator')
                ->color('success') // Verde dinero
                ->modalHeading('Resumen del Autoavalúo Calculado')
                ->modalDescription('Cálculo basado en Valores Unitarios y Aranceles vigentes.')
                ->modalSubmitAction(false) // Ocultamos botón de "Guardar" porque es solo vista
                ->modalCancelActionLabel('Cerrar')
                ->fillForm(function (PredioFisico $record): array {
                    // 1. Instanciamos el servicio
                    $service = new CalculoAutoavaluoService($record);

                    // 2. Ejecutamos el cálculo
                    try {
                        return $service->calcularTotal();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error de Cálculo')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                        return [];
                    }
                })
                ->form([
                    Section::make()
                        ->schema([
                            Grid::make(2)->schema([
                                // A. Terreno
                                TextInput::make('valor_terreno')
                                    ->label('Valor del Terreno')
                                    ->prefix('S/.')
                                    ->dehydrated(false)
                                    ->readOnly()
                                    ->formatStateUsing(fn($state) => number_format((float) $state, 2, '.', ',')), // Solo lectura

                                // B. Construcción
                                TextInput::make('valor_construccion')
                                    ->label('Valor de Construcción')
                                    ->prefix('S/.')
                                    ->dehydrated(false)
                                    ->readOnly()
                                    ->formatStateUsing(fn($state) => number_format((float) $state, 2, '.', ',')),

                                // C. Obras
                                TextInput::make('valor_obras')
                                    ->label('Obras Complementarias')
                                    ->prefix('S/.')
                                    ->dehydrated(false)
                                    ->readOnly()
                                    ->formatStateUsing(fn($state) => number_format((float) $state, 2, '.', ',')),

                                // D. TOTAL
                                TextInput::make('total_autoavaluo')
                                    ->label('TOTAL AUTOAVALÚO')
                                    ->prefix('S/.')
                                    ->extraInputAttributes(['style' => 'font-weight: 900; font-size: 1.5rem; color: #166534; text-align: right;'])
                                    ->dehydrated(false)
                                    ->readOnly()
                                    ->formatStateUsing(fn($state) => number_format((float) $state, 2, '.', ',')),
                            ]),
                        ])
                ]),*/

            DeleteAction::make(),
        ];
    }

    // INTERCEPTAMOS EL GUARDADO
    /*
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // 1. Extraemos la decisión del usuario
        $tipoEdicion = $data['tipo_edicion'] ?? 'actualizacion';

        // 2. Limpiamos el array para que no intente guardar 'tipo_edicion' en la tabla
        unset($data['tipo_edicion']);

        if ($tipoEdicion === 'correccion') {
            // Opción A: Actualización normal (UPDATE)
            $record->update($data);
            return $record;
        } else {
            // Opción B: Nueva Versión (INSERT + HISTORY)
            // Usamos nuestro Trait HasHistory
            $nuevoRegistro = $record->createNewVersion($data);

            return $nuevoRegistro;
        }
    }

    // Opcional: Redirigir al usuario al nuevo registro después de guardar
    // Si no hacemos esto, se quedará viendo la versión "vieja" (inactiva)
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
    */
}
