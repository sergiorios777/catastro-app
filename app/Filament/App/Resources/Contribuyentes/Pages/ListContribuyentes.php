<?php

namespace App\Filament\App\Resources\Contribuyentes\Pages;

use App\Filament\App\Resources\Contribuyentes\ContribuyenteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\App\Resources\Contribuyentes;
use App\Models\AnioFiscal;
use App\Models\Persona;
use App\Jobs\CalcularImpuestoJob;
use Filament\Actions;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Bus;
use Illuminate\Database\Eloquent\Builder;

class ListContribuyentes extends ListRecords
{
    protected static string $resource = ContribuyenteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('emision_masiva')
                ->label('Ejecutar Emisión Masiva')
                ->color('primary')
                ->form([
                    Select::make('anio_fiscal_id')
                        ->label('Año Fiscal')
                        ->options(AnioFiscal::pluck('anio', 'id'))
                        ->required()
                        ->default(fn() => AnioFiscal::where('anio', date('Y'))->value('id')),
                ])
                ->action(function (array $data) {
                    $anioId = $data['anio_fiscal_id'];
                    $anioFiscal = AnioFiscal::find($anioId);

                    if (!$anioFiscal) {
                        Notification::make()->danger()->title('Año Fiscal inválido')->send();
                        return;
                    }

                    // 1. Identificar IDs pendientes
                    // Replicamos la logica del Tab 'pendientes' pero obteniendo IDs
                    $idsPendientes = Persona::query()
                        ->whereHas('predioFisicos', function ($q) {
                        $q->where('predios_fisicos.tenant_id', filament()->getTenant()->id);
                    })
                        ->whereDoesntHave('determinaciones', function ($q) use ($anioId) {
                        $q->where('anio_fiscal_id', $anioId);
                    })
                        ->pluck('id');

                    if ($idsPendientes->isEmpty()) {
                        Notification::make()->warning()->title('No hay pendientes para procesar')->send();
                        return;
                    }

                    // 2. Despachar Batch de Jobs
                    $jobs = $idsPendientes->map(fn($id) => new CalcularImpuestoJob($id, (int) $anioFiscal->anio));

                    Bus::batch($jobs)
                        ->name('Emisión Masiva Predial ' . $anioFiscal->anio)
                        ->dispatch();

                    Notification::make()
                        ->title('Proceso en segundo plano iniciado')
                        ->body("Se están procesando {$idsPendientes->count()} contribuyentes.")
                        ->success()
                        ->send();
                }),
            //CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        // Recuperar el año real de la selección del usuario o sesión.
        // Por ahora hardcodeamos al año actual o lógica de BD.
        $anio = date('Y');
        // Idealmente: $anioId = AnioFiscal::where('anio', $anio)->value('id');
        // Pero para la query 'whereDoesntHave', necesitamos filtrar por la relación.

        // Asumo que DeterminacionPredial tiene relación 'anioFiscal' que tiene columna 'anio',
        // O DeterminacionPredial tiene 'anio_fiscal_id'. 
        // Vamos a filtrar por el AÑO FISCAL ID si es posible, o por el año si tenemos la join.
        // Simplificación: Usaremos whereHas('anioFiscal', function($q) use ($anio) { $q->where('anio', $anio); })

        return [
            'pendientes' => Tab::make('Pendientes de Cálculo')
                ->icon('heroicon-m-clock')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDoesntHave(
                    'determinaciones',
                    fn($q) =>
                    $q->whereHas('anioFiscal', fn($sub) => $sub->where('anio', $anio))
                )),

            'procesados' => Tab::make('Ya Calculados')
                ->icon('heroicon-m-check-badge')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas(
                    'determinaciones',
                    fn($q) =>
                    $q->whereHas('anioFiscal', fn($sub) => $sub->where('anio', $anio))
                )),
        ];
    }
}
