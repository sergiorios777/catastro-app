<?php

namespace App\Filament\App\Resources\Personas\Tables;

use App\Services\CalculoImpuestoService;
use App\Models\Persona;
use App\Models\PropietarioPredio;
use Filament\Notifications\Notification;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class PersonasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_documento')
                    ->searchable()
                    ->sortable()
                    ->label('Documento'),

                TextColumn::make('nombre_completo')
                    ->label('Nombre / Razón Social')
                    ->searchable(['nombres', 'apellidos', 'razon_social'])
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('apellidos', $direction)
                            ->orderBy('razon_social', $direction);
                    }),

                TextColumn::make('tipo_persona')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'natural' => 'success',
                        'juridica' => 'info',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),

                TextColumn::make('direccion')
                    ->label('Dirección')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('telefono')
                    ->icon('heroicon-m-phone')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo_persona')
                    ->options([
                        'natural' => 'Natural',
                        'juridica' => 'Jurídica',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('calcular_impuesto')
                    ->label('Calc. Predial')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Calcular Impuesto Predial 2025')
                    ->modalDescription('Esto sumará todos los predios de la persona y actualizará su deuda anual.')
                    ->action(function (Persona $record) {
                        try {
                            // Asumimos año 2025 (puedes hacerlo dinámico luego con un Select en el modal)
                            $anio = 2025;

                            $service = new CalculoImpuestoService($record, $anio);
                            $determinacion = $service->generarDeterminacion();

                            Notification::make()
                                ->title('Cálculo Exitoso')
                                ->body("Impuesto Calculado: S/. " . number_format((float) $determinacion->impuesto_calculado, 2))
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body($e->getMessage()) // Probablemente falte configurar la UIT
                                ->danger()
                                ->send();
                        }
                    })
                    // --- NEW: VISIBILITY CONDITION ---
                    ->visible(function (Persona $record) {
                        // Check if there is at least one property linked to this person
                        // that belongs to the current Tenant AND is marked as 'vigente'
                        return PropietarioPredio::where('persona_id', $record->id)
                            ->where('tenant_id', auth()->user()->tenant_id) // Strict check for current municipality
                            ->where('vigente', true) // Only if they currently own it
                            ->exists();
                    }),
                Action::make('imprimir_hr')
                    ->label('Imprimir HR')
                    ->icon('heroicon-o-document-text')
                    ->color('success') // Verde
                    ->url(fn(Persona $record) => route('imprimir.hr', [
                        // Ojo: Aquí necesitamos buscar la ID de la determinación de este año
                        'id' => \App\Models\DeterminacionPredial::where('persona_id', $record->id)
                            ->where('anio_fiscal_id', \App\Models\AnioFiscal::where('anio', 2025)->first()->id) // Ajustar año dinámico
                            ->first()?->id
                    ]))
                    ->openUrlInNewTab() // Abrir PDF en otra pestaña
                    ->visible(fn(Persona $record) => \App\Models\DeterminacionPredial::where('persona_id', $record->id)
                        ->where('anio_fiscal_id', \App\Models\AnioFiscal::where('anio', 2025)->first()->id)
                        ->exists()), // Solo mostrar si ya se calculó el impuesto

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
