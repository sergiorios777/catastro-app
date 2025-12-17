<?php

namespace App\Filament\Imports;

use App\Models\AnioFiscal;
use App\Models\CatalogoObraComplementaria;
use App\Models\ValorObraComplementaria;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ValorObraComplementariaImporter extends Importer
{
    protected static ?string $model = ValorObraComplementaria::class;

    public static function getColumns(): array
    {
        return [
            // 1. Relación con Año Fiscal (por columna 'anio' del CSV)
            ImportColumn::make('anioFiscal')
                ->relationship(resolveUsing: 'anio')
                ->rules(['required']),

            // 2. Relación con la Obra (por columna 'codigo' del CSV)
            // Esto es crucial: buscamos la obra por su código (ej: '01.01')
            ImportColumn::make('obra')
                ->relationship(resolveUsing: 'codigo')
                ->rules(['required']),

            // 3. Zona y Precio
            ImportColumn::make('zona_geografica')
                ->requiredMapping()
                ->rules(['required']),

            ImportColumn::make('valor')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),
        ];
    }

    public function resolveRecord(): ?ValorObraComplementaria
    {
        /*return ValorObraComplementaria::firstOrNew([
            'anio_fiscal_id' => $this->data['anioFiscal'],
            'catalogo_obra_complementaria_id' => $this->data['obra'],
            'zona_geografica' => $this->data['zona_geografica'],
        ]);*/
        // 1. Buscamos manualmente el ID del Año Fiscal
        // Buscamos en la tabla 'anio_fiscals' donde la columna 'anio' coincida con el CSV
        $anioId = AnioFiscal::where('anio', $this->data['anioFiscal'])->value('id');

        // 2. Buscamos manualmente el ID de la Obra
        // Buscamos en la tabla 'catalogo...' donde el 'codigo' coincida con el CSV
        $obraId = CatalogoObraComplementaria::where('codigo', $this->data['obra'])->value('id');

        // (Opcional) Si no encontramos alguno, podríamos lanzar error, 
        // pero por ahora dejemos que falle naturalmente o retorne null si prefieres.
        if (!$anioId || !$obraId) {
            return null; // Esto marcará la fila como fallida en 'failed_import_rows'
        }

        // 3. Retornamos el modelo con los IDs numéricos correctos
        return ValorObraComplementaria::firstOrNew([
            'anio_fiscal_id' => $anioId,
            'catalogo_obra_complementaria_id' => $obraId,
            'zona_geografica' => $this->data['zona_geografica'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your valor obra complementaria import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
