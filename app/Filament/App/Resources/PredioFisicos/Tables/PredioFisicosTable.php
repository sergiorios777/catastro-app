<?php

namespace App\Filament\App\Resources\PredioFisicos\Tables;

use App\Filament\App\Resources\PredioFisicos\Schemas\PredioFisicoForm;
use App\Models\PredioFisico;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Radio;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PredioFisicosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with('predioFisicoAvaluos'))
            ->columns([
                // Columna Inteligente de CUC
                TextColumn::make('cuc')
                    ->label('CUC')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(PredioFisico $record): string => $record->es_cuc_provisional ? 'warning' : 'success')
                    ->icon(fn(PredioFisico $record): string => $record->es_cuc_provisional ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-badge')
                    ->tooltip(fn(PredioFisico $record): string => $record->es_cuc_provisional ? 'Código generado internamente (Provisional)' : 'Código Oficial Validado'),

                TextColumn::make('direccion')
                    ->label('Dirección')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('ubicacion_corta')
                    ->label('Mza / Lote')
                    ->state(fn(PredioFisico $record) => "{$record->manzana} - {$record->lote}")
                    ->sortable(['manzana', 'lote']),

                TextColumn::make('area_terreno')
                    ->label('Área')
                    ->state(function (PredioFisico $record) {
                        return $record->predioFisicoAvaluos->sortByDesc('version')->first()?->area_terreno;
                    })
                    ->numeric(2)
                    ->suffix(' m²')
                    ->sortable(),

                TextColumn::make('tipo_predio')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->color(fn(string $state): string => match ($state) {
                        'urbano' => 'info',
                        'rustico' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'activo' ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('tipo_predio')
                    ->options([
                        'urbano' => 'Urbano',
                        'rustico' => 'Rústico',
                    ]),
                SelectFilter::make('estado')
                    ->default('activo') // Por defecto ver solo activos
                    ->options([
                        'activo' => 'Activo',
                        'fusionado' => 'Fusionado',
                        'dividido' => 'Dividido',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Editar')
                        ->modalHeading('Actualizar Ficha Predial')
                        ->modalWidth('7xl')
                        // --- THE FIX: Use Schema $schema instead of Form $form ---
                        ->form(function (Schema $schema) {
                            return $schema->components([
                                // A. Load original components
                                Section::make('Datos del Predio')
                                    ->schema(PredioFisicoForm::getComponents())
                                    ->columns(2),

                                // B. Version Control Question
                                Section::make('Tipo de Operación')
                                    ->schema([
                                        Radio::make('tipo_edicion')
                                            ->label('¿Qué tipo de cambio está realizando?')
                                            ->options([
                                                'correccion' => 'Corrección de error (No genera historial)',
                                                'actualizacion' => 'Actualización Física (Genera historial)',
                                            ])
                                            ->default('correccion')
                                            ->formatStateUsing(fn($state) => $state ?? 'correccion')
                                            ->required()
                                            ->columnSpanFull(),
                                    ])
                                    ->secondary()
                                    ->icon('heroicon-o-exclamation-triangle'),
                            ]);
                        })
                        ->using(function (Model $record, array $data): Model {
                            $tipoEdicion = $data['tipo_edicion'] ?? 'actualizacion';
                            unset($data['tipo_edicion']);

                            if ($tipoEdicion === 'correccion') {
                                $record->update($data);
                                return $record;
                            } else {
                                return $record->createNewVersion($data);
                            }
                        })
                        ->successNotificationTitle('Predio actualizado correctamente'),

                    // Botón de historial (justo debajo del EditAction)
                    Action::make('historial')
                        ->label('Historial')
                        ->icon('heroicon-m-clock')
                        ->color('gray')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Cerrar')
                        ->modalContent(fn($record) => view('filament.components.history-modal', [
                            'records' => $record->history()->get()
                        ])),

                    // 3. ACCIÓN DE IMPRIMIR TICKETS
                    Action::make('imprimir_pu')
                        ->label('PU')
                        ->icon('heroicon-o-document')
                        ->color('info') // Azul
                        ->url(fn(PredioFisico $record) => route('imprimir.pu', $record->id))
                        ->openUrlInNewTab(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
