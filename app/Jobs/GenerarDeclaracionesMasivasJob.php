<?php

namespace App\Jobs;

use App\Models\AnioFiscal;
use App\Models\DeterminacionPredial;
use App\Services\PdfGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use ZipArchive;
use Illuminate\Support\Str;

class GenerarDeclaracionesMasivasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $anio;
    public $userId; // Para notificar al usuario que lo pidió
    public $tenantId;

    public $timeout = 3600; // 1 hora de timeout si son muchos

    public function __construct(int $anio, int $userId, int $tenantId)
    {
        $this->anio = $anio;
        $this->userId = $userId;
        $this->tenantId = $tenantId;
    }

    public function handle(PdfGeneratorService $pdfService): void
    {
        $anioId = AnioFiscal::where('anio', $this->anio)->value('id');

        // USAR DISCO PÚBLICO EXPLÍCITAMENTE
        $disk = Storage::disk('public');

        // 1. Crear carpeta temporal única en PUBLIC
        $batchId = Str::uuid();
        $basePath = "exports/{$batchId}";
        $disk->makeDirectory($basePath);

        // 2. Obtener determinaciones calculadas // 'predio'
        $determinaciones = DeterminacionPredial::with(['persona', 'predio', 'anioFiscal', 'tenant'])
            ->where('anio_fiscal_id', $anioId)
            ->where('tenant_id', $this->tenantId)
            ->cursor();

        $count = 0;
        $errors = 0;

        foreach ($determinaciones as $det) {
            $contribuyente = $det->persona;
            // Limpiamos el nombre para evitar errores de ruta en Windows/Linux
            $cleanName = Str::slug($contribuyente->nombre_completo);
            $folderName = "{$contribuyente->numero_documento}-{$cleanName}";

            // Ruta relativa dentro del disco public
            $contribuyentePath = "{$basePath}/{$folderName}";

            // Intentar crear el directorio
            $disk->makeDirectory($contribuyentePath);

            // Obtener nombre de usuario para el reporte
            $user = \App\Models\User::find($this->userId);
            $userName = $user ? $user->name : 'Sistema';

            try {
                // A. Generar HR
                $hrContent = $pdfService->generateHrContent($det, $userName);

                if (empty($hrContent)) {
                    throw new \Exception("El contenido del PDF HR llegó vacío.");
                }

                $disk->put("{$contribuyentePath}/HR-{$this->anio}.pdf", $hrContent);

                // B. Generar PUs
                $predios = $det->persona->predioFisicos()
                    ->where('predios_fisicos.tenant_id', $this->tenantId)
                    ->wherePivot('vigente', true)
                    ->get();

                if ($predios->isEmpty()) {
                    \Log::warning("El contribuyente {$contribuyente->numero_documento} tiene determinación pero no predios vigentes vinculados.");
                }

                foreach ($predios as $predio) {
                    $puContent = $pdfService->generatePuContent($predio, $this->anio);
                    if (!empty($puContent)) {
                        // CORRECCIÓN: Usamos el CUC como identificador principal
                        // Si por alguna razón extrema no tiene CUC (ej. error de migración), usamos el ID como respaldo.
                        $identificador = !empty($predio->cuc) ? $predio->cuc : "ID-{$predio->id}";

                        // Nombre Final: PU-[CUC].pdf
                        // Ejemplo: PU-12000045881.pdf
                        $fileName = "PU-{$identificador}.pdf";

                        $disk->put("{$contribuyentePath}/{$fileName}.pdf", $puContent);
                    }
                }

                $count++;

            } catch (\Throwable $e) { // Usar Throwable captura errores fatales también
                $errors++;
                // IMPORTANTE: Esto aparecerá en tu laravel.log explicándote por qué la carpeta está vacía
                \Log::error("FALLO PDF Contribuyente {$contribuyente->numero_documento}: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());

                // Opcional: Eliminar la carpeta vacía si falló todo
                // $disk->deleteDirectory($contribuyentePath);
            }
        }

        if ($count === 0) {
            $this->notifyError("Proceso terminado sin archivos. Hubo {$errors} errores. Revise el log.");
            // Limpieza aunque fallara
            $disk->deleteDirectory($basePath);
            return;
        }

        // 3. Comprimir en ZIP
        $zipFileName = "Declaraciones_{$this->anio}_{$batchId}.zip";

        // Obtenemos la ruta ABSOLUTA del sistema de archivos para ZipArchive
        $zipAbsolutePath = $disk->path("exports/{$zipFileName}");

        $zip = new ZipArchive;

        if ($zip->open($zipAbsolutePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // Obtenemos todos los archivos generados en la carpeta temporal
            $files = $disk->allFiles($basePath);

            foreach ($files as $file) {
                // $file es relativo: "exports/uuid/carpeta/archivo.pdf"
                // Queremos que en el zip se vea: "carpeta/archivo.pdf"
                $nameInZip = str_replace("{$basePath}/", '', $file);

                // Leemos el contenido del disco y lo metemos al zip
                $zip->addFromString($nameInZip, $disk->get($file));
            }
            $zip->close();
        } else {
            \Log::error("No se pudo crear el archivo ZIP en: $zipAbsolutePath");
            $this->notifyError("Error al comprimir los archivos.");
            return;
        }

        // 4. Limpiar carpeta temporal (Los PDFs sueltos)
        $disk->deleteDirectory($basePath);

        // 5. Notificar éxito con link
        // 'storage' en la URL apunta al disco public si hiciste el link simbólico
        $url = asset("storage/exports/{$zipFileName}");

        try {
            $this->notifySuccess($url, $count);
        } catch (\Throwable $e) {
            \Log::error("ZIP creado en $url pero falló notificación: " . $e->getMessage());
        }
    }

    private function notifySuccess($url, $count)
    {
        Notification::make()
            ->title('Exportación Completada')
            ->body("Se han generado {$count} carpetas de declaraciones juradas.")
            ->success()
            ->actions([
                Action::make('descargar')
                    ->button()
                    ->url($url)
                    ->openUrlInNewTab(),
            ])
            ->sendToDatabase(\App\Models\User::find($this->userId));
    }

    private function notifyError($msg)
    {
        Notification::make()
            ->title('Error en Exportación')
            ->body($msg)
            ->danger()
            ->sendToDatabase(\App\Models\User::find($this->userId));
    }
}
