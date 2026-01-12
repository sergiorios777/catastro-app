<?php

namespace App\Filament\App\Resources\Contribuyentes\Pages;

use App\Filament\App\Resources\Contribuyentes\ContribuyenteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\App\Resources\Contribuyentes;
use App\Models\AnioFiscal;
use App\Models\Persona;
use App\Models\DeterminacionPredial;
use App\Jobs\CalcularImpuestoJob;
use App\Jobs\GenerarDeclaracionesMasivasJob;
use Filament\Actions;
use Filament\Actions\Action;
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

                    // 2. Preparar variables para el contexto del Batch
                    // IMPORTANTE: Capturamos datos simples (ID, string) porque el closure del Batch se serializa
                    $userId = auth()->id();
                    $anioActual = $this->anio;
                    $cantidad = $idsPendientes->count();

                    // 3. Crear instancias de Jobs
                    $jobs = $idsPendientes->map(fn($id) => new CalcularImpuestoJob($id, (int) $anioActual));

                    // 4. Despachar Batch con Callback de Finalización
                    Bus::batch($jobs)
                        ->name('Emisión Masiva Predial ' . $anioActual)
                        ->allowFailures() // Permite que el lote continúe si falla un solo contribuyente
                        ->finally(function (\Illuminate\Bus\Batch $batch) use ($userId, $anioActual, $cantidad) {
                        // ESTE BLOQUE SE EJECUTA EN EL WORKER AL TERMINAR TODO
        
                        $user = \App\Models\User::find($userId);

                        if ($user) {
                            // Determinamos si hubo fallos para cambiar el mensaje
                            $mensaje = $batch->hasFailures()
                                ? "Proceso finalizado con algunos errores. Se procesaron {$cantidad} registros."
                                : "Se completó el cálculo de impuestos para {$cantidad} contribuyentes del año {$anioActual}.";

                            $tipo = $batch->hasFailures() ? 'warning' : 'success';

                            Notification::make()
                                        ->title('Cálculo Masivo Finalizado')
                                        ->body($mensaje)
                                ->$tipo()
                                    ->actions([
                                        Action::make('ver')
                                            ->label('Ver Resultados')
                                            ->button()
                                            // Usamos url() helper, asegurate que la ruta sea correcta para tu admin
                                            ->url('/admin/contribuyentes?activeTab=procesados'),
                                    ])
                                    ->sendToDatabase($user); // <--- Aquí es donde ocurre la magia
                        }
                    })
                        ->dispatch();

                    // Notificación inmediata (Toast) para decir "Ya empezamos"
                    Notification::make()
                        ->title('Proceso iniciado en segundo plano')
                        ->body("Procesando {$cantidad} contribuyentes. Te avisaremos en la campanita al terminar.")
                        ->success()
                        ->send();
                }),

            // NUEVA ACCIÓN: IMPRESIÓN MASIVA
            Actions\Action::make('imprimir_masivo')
                ->label('Descargar HR/PU Masivo (' . $this->anio . ')')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Generación Masiva de PDFs')
                ->modalDescription('Este proceso generará un archivo ZIP con todas las declaraciones juradas (HR y PU) de los contribuyentes que YA tienen el impuesto calculado para este año. Puede tardar varios minutos.')
                ->action(function () {
                    // Verificar si hay año fiscal
                    $anioId = AnioFiscal::where('anio', $this->anio)->value('id');
                    if (!$anioId) {
                        Notification::make()->danger()->title('Año Fiscal no configurado')->send();
                        return;
                    }

                    // Verificar si hay algo calculado
                    $count = DeterminacionPredial::where('anio_fiscal_id', $anioId)
                        ->where('tenant_id', filament()->getTenant()->id)
                        ->count();

                    if ($count === 0) {
                        Notification::make()->warning()
                            ->title("No hay impuestos calculados para el año {$this->anio}")
                            ->body("Primero ejecute la 'Emisión Masiva' de cálculo.")
                            ->send();
                        return;
                    }

                    // Despachar Job
                    GenerarDeclaracionesMasivasJob::dispatch(
                        (int) $this->anio,
                        auth()->id(),
                        filament()->getTenant()->id
                    );

                    Notification::make()
                        ->title('Generación de PDFs iniciada')
                        ->body('Recibirá una notificación cuando el archivo ZIP esté listo para descargar.')
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
