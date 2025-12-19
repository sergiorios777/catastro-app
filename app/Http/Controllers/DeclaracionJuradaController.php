<?php

namespace App\Http\Controllers;

use App\Models\DeterminacionPredial;
use App\Models\PropietarioPredio;
use App\Models\PredioFisico;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DeclaracionJuradaController extends Controller
{
    public function imprimirHr($determinacionId)
    {
        // 1. Recuperamos la Determinación (La Cabecera del HR)
        $determinacion = DeterminacionPredial::with(['persona', 'anioFiscal', 'tenant'])
            ->findOrFail($determinacionId);

        // 2. Recuperamos los Predios asociados a esa persona en ese año
        // LÓGICA HÍBRIDA:
        if (!empty($determinacion->snapshot_datos)) {
            // A. VIAJE EN EL TIEMPO: Usamos el Snapshot JSON
            // Convertimos el array a objetos genéricos para que Blade no se queje
            // (json_decode con false devuelve objetos stdClass)
            $prediosData = json_decode(json_encode($determinacion->snapshot_datos), false);

            // --- CORRECCIÓN AQUÍ ---
            // Mapeamos para restaurar la compatibilidad de nombres (snake_case -> camelCase)
            $predios = collect($prediosData)->map(function ($item) {
                // El problema: JSON tiene 'predio_fisico', Blade espera 'predioFisico'
                if (isset($item->predio_fisico)) {
                    $item->predioFisico = $item->predio_fisico;
                }

                // Si hubiera otras relaciones anidadas, también se ajustan aquí si fuera necesario
                return $item;
            });

        } else {
            // B. MODO ACTUAL: Usamos la BD en vivo (Para registros viejos sin snapshot)
            $predios = PropietarioPredio::with('predioFisico')
                ->where('persona_id', $determinacion->persona_id)
                ->where('tenant_id', $determinacion->tenant_id)
                ->where('vigente', true)
                ->get();
        }

        // 3. Generamos el PDF usando una vista Blade
        $pdf = Pdf::loadView('pdfs.hr', [
            'determinacion' => $determinacion,
            'predios' => $predios,
            'anio' => $determinacion->anioFiscal->anio,
            'municipio' => $determinacion->tenant
        ]);

        // 4. Configuración de hoja (A4 Vertical es estándar para HR)
        $pdf->setPaper('a4', 'portrait');

        // 5. Descargar o Ver en navegador (stream es mejor para previsualizar)
        return $pdf->stream("HR-{$determinacion->anioFiscal->anio}-{$determinacion->persona->numero_documento}.pdf");
    }

    public function imprimirPu($predioId)
    {
        // 1. Cargamos el Predio con TODO su detalle
        $predio = PredioFisico::with([
            'construcciones',
            'obrasComplementarias',
            'tenant',
            // Cargamos propietarios para mostrar quién declara
            'propietarios'
        ])->findOrFail($predioId);

        // 2. Detectar al propietario principal
        // En una relación belongsToMany, los elementos de $predio->propietarios SON las Personas.
        // Los datos de la tabla intermedia (vigente, porcentaje) están en ->pivot

        $persona = $predio->propietarios
            ->filter(function ($propietario) {
                // Accedemos al dato 'vigente' a través del pivote
                return $propietario->pivot->vigente;
            })
            ->first();

        // 3. Renderizar PDF
        $pdf = Pdf::loadView('pdfs.pu', [
            'predio' => $predio,
            'persona' => $persona,
            'anio' => 2025, // Ojo: Deberíamos recibir el año por parámetro idealmente
            'municipio' => $predio->tenant
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream("PU-{$predio->codigo_referencia}.pdf");
    }
}
