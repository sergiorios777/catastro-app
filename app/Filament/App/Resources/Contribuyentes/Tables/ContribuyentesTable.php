<?php

namespace App\Filament\App\Resources\Contribuyentes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use App\Models\Persona;
use App\Models\AnioFiscal;
use App\Models\PropietarioPredio;
use App\Services\CalculoImpuestoService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Filament\Actions\Action;

class ContribuyentesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre_completo') // Usando el accessor de Persona
                    ->label('Contribuyente')
                    ->searchable(['nombres', 'apellidos', 'razon_social'])
                    ->sortable(),

                TextColumn::make('numero_documento')
                    ->label('Documento')
                    ->searchable(),

                TextColumn::make('predio_fisicos_count')
                    ->counts('predioFisicos')
                    ->label('N° Predios')
                    ->badge()
                    ->color(fn(int $state): string => $state > 5 ? 'warning' : 'success')
                    ->sortable(),

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
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('calcular')
                    ->label('Calcular')
                    ->icon('heroicon-o-calculator')
                    ->requiresConfirmation()
                    ->form([
                        Select::make('anio_fiscal_id')
                            ->label('Año Fiscal')
                            ->options(AnioFiscal::pluck('anio', 'id'))
                            ->required()
                            ->default(fn() => AnioFiscal::where('anio', date('Y'))->value('id')),
                    ])
                    ->action(function (Persona $record, array $data) {
                        try {
                            // Obtener año. TODO: obtener de conf global
                            // $anio = date('Y'); // Placeholder
                            $anioId = $data['anio_fiscal_id'];
                            $anio = AnioFiscal::find($anioId)->anio;

                            DB::beginTransaction();
                            // Instanciar servicio manualmente
                            $service = new CalculoImpuestoService($record, $anio);
                            $service->generarDeterminacion();
                            DB::commit();

                            Notification::make()->success()->title('Cálculo Exitoso')->send();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Notification::make()->danger()->title('Error')->body($e->getMessage())->send();
                        }
                    }),
                Action::make('imprimir_hr')
                    ->label('Imprimir HR')
                    ->icon('heroicon-o-document-text')
                    ->color('success') // Verde
                    ->url(function (Persona $record, $livewire) {
                        $anio = $livewire->anio ?? date('Y');
                        $anioId = AnioFiscal::where('anio', $anio)->value('id');

                        return route('imprimir.hr', [
                            'id' => \App\Models\DeterminacionPredial::where('persona_id', $record->id)
                                ->where('anio_fiscal_id', $anioId)
                                ->first()?->id
                        ]);
                    })
                    ->openUrlInNewTab() // Abrir PDF en otra pestaña
                    ->visible(function (Persona $record, $livewire) {
                        $anio = $livewire->anio ?? date('Y');
                        $anioId = AnioFiscal::where('anio', $anio)->value('id');

                        return \App\Models\DeterminacionPredial::where('persona_id', $record->id)
                            ->where('anio_fiscal_id', $anioId)
                            ->exists();
                    }), // Solo mostrar si ya se calculó el impuesto
                //EditAction::make(),
            ])
            ->toolbarActions([
                /*
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
                */
            ]);
    }
}
