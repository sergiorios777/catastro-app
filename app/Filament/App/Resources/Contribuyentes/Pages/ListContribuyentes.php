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
use Livewire\Attributes\Url;

class ListContribuyentes extends ListRecords
{
    protected static string $resource = ContribuyenteResource::class;

    #[Url]
    public ?string $anio = null;

    public function mount(): void
    {
        $this->anio = $this->anio ?? (string) date('Y');
        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cambiar_anio')
                ->label('Cambiar Año: ' . $this->anio)
                ->icon('heroicon-m-calendar')
                ->color('gray')
                ->form([
                    Select::make('anio')
                        ->label('Año Fiscal')
                        ->options(AnioFiscal::pluck('anio', 'anio'))
                        ->default($this->anio)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->redirect(ListContribuyentes::getUrl(['anio' => $data['anio']]));
                }),

            Actions\Action::make('emision_masiva')
                ->label('Ejecutar Emisión Masiva (' . $this->anio . ')')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function () {
                    $anioId = AnioFiscal::where('anio', $this->anio)->value('id');

                    if (!$anioId) {
                        Notification::make()->danger()->title('Año Fiscal no configurado para ' . $this->anio)->send();
                        return;
                    }

                    // 1. Identificar IDs pendientes
                    $idsPendientes = Persona::query()
                        ->whereHas('predioFisicos', function ($q) {
                        $q->where('predios_fisicos.tenant_id', filament()->getTenant()->id);
                    })
                        ->whereDoesntHave('determinaciones', function ($q) use ($anioId) {
                        $q->where('anio_fiscal_id', $anioId);
                    })
                        ->pluck('id');

                    if ($idsPendientes->isEmpty()) {
                        Notification::make()->warning()->title('No hay pendientes para procesar en ' . $this->anio)->send();
                        return;
                    }

                    // 2. Despachar Batch de Jobs
                    // Nota: CalcularImpuestoJob espera ID y AÑO (int).
                    $jobs = $idsPendientes->map(fn($id) => new CalcularImpuestoJob($id, (int) $this->anio));

                    Bus::batch($jobs)
                        ->name('Emisión Masiva Predial ' . $this->anio)
                        ->dispatch();

                    Notification::make()
                        ->title('Proceso iniciado')
                        ->body("Procesando {$idsPendientes->count()} contribuyentes para el año {$this->anio}.")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        $anio = $this->anio;

        return [
            'pendientes' => Tab::make('Pendientes ' . $anio)
                ->icon('heroicon-m-clock')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDoesntHave(
                    'determinaciones',
                    fn($q) =>
                    $q->whereHas('anioFiscal', fn($sub) => $sub->where('anio', $anio))
                ))
                ->badge(fn() => Persona::whereHas('predioFisicos', function ($q) {
                    $q->where('predios_fisicos.tenant_id', filament()->getTenant()->id);
                })->whereDoesntHave(
                        'determinaciones',
                        fn($q) =>
                        $q->whereHas('anioFiscal', fn($sub) => $sub->where('anio', $anio))
                    )->count()),

            'procesados' => Tab::make('Calculados ' . $anio)
                ->icon('heroicon-m-check-badge')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas(
                    'determinaciones',
                    fn($q) =>
                    $q->whereHas('anioFiscal', fn($sub) => $sub->where('anio', $anio))
                )),
        ];
    }
}
