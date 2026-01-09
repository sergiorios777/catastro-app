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
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Actions; // Contenedor de actions dentro del infolist
use Filament\Actions\Action as InfolistAction; // Action dentro del infolist (unificado)
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\ActionGroup;

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
                ActionGroup::make([
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
                    ,
                    Action::make('imprimir_pu')
                        ->label('Imprimir PU')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->modalHeading(fn($record) => "Formatos PU: {$record->nombre_completo}")
                        ->modalDescription('Seleccione el predio que desea imprimir.')
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        // CAMBIO AQUÍ: El tipo de dato ahora es Schema
                        ->infolist(function (Schema $schema, $livewire) {

                            $anioSeleccionado = $livewire->anio ?? date('Y');

                            return $schema // Ahora trabajamos sobre el objeto Schema
                                ->components([ // En v4, usamos 'components' en lugar de 'schema' dentro del objeto Schema
                                    RepeatableEntry::make('predioFisicos')
                                        ->label('')
                                        ->schema([ // Dentro del componente sí seguimos usando schema
                                            Grid::make(3)->schema([
                                                TextEntry::make('cuc')
                                                    ->label('CUC')
                                                    ->icon('heroicon-m-hashtag')
                                                    ->helperText(fn($record) => $record->codigo_referencia ? "Ref: {$record->codigo_referencia}" : null),

                                                TextEntry::make('direccion')
                                                    ->label('Dirección')
                                                    ->icon('heroicon-m-map-pin')
                                                    ->limit(40),

                                                Actions::make([
                                                    InfolistAction::make('descargar_pdf')
                                                        ->label('Imprimir')
                                                        ->icon('heroicon-o-printer')
                                                        ->color('success')
                                                        ->button()
                                                        ->size('xs')
                                                        ->url(fn($record) => route('imprimir.pu', [
                                                            'predioId' => $record->id,
                                                            'anio' => $anioSeleccionado
                                                        ]))
                                                        ->openUrlInNewTab(),
                                                ])->alignEnd(),
                                            ]),
                                        ])
                                        ->grid(1)
                                        ->contained(true)
                                ]);
                        }),
                ])
                    ->visible(function (Persona $record, $livewire) {
                        $anio = $livewire->anio ?? date('Y');
                        $anioId = AnioFiscal::where('anio', $anio)->value('id');

                        return \App\Models\DeterminacionPredial::where('persona_id', $record->id)
                            ->where('anio_fiscal_id', $anioId)
                            ->exists();
                    }) // Solo mostrar si ya se calculó el impuesto
                    ->label('Formatos')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->button(),
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
