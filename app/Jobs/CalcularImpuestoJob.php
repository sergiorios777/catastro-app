<?php

namespace App\Jobs;

use App\Models\Persona;
use App\Services\CalculoImpuestoService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalcularImpuestoJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $personaId;
    public $anio;

    /**
     * Create a new job instance.
     */
    public function __construct($personaId, int $anio)
    {
        $this->personaId = $personaId;
        $this->anio = $anio;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        try {
            $persona = Persona::find($this->personaId);

            if (!$persona) {
                Log::warning("CalcularImpuestoJob: Persona ID {$this->personaId} no encontrada.");
                return;
            }

            // Instanciar el servicio (La inyección de dependencias automática en Jobs a veces es truculenta con constructores custom,
            // mejor instanciamos manualmente o usamos app() si el servicio tuviera dependencias complejas).
            // El servicio CalculoImpuestoService requiere (Persona $persona, int $anio) en constructor.

            $service = new CalculoImpuestoService($persona, $this->anio);
            $service->generarDeterminacion();

        } catch (\Throwable $e) {
            Log::error("Error en CalcularImpuestoJob para Persona {$this->personaId}: " . $e->getMessage());
            $this->fail($e);
        }
    }
}
