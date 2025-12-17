<?php

namespace App\Filament\Imports;

use App\Models\CatalogoObraComplementaria;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class CatalogoObraComplementariaImporter extends Importer
{
    protected static ?string $model = CatalogoObraComplementaria::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('codigo')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('descripcion')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('unidad_medida')
                ->requiredMapping()
                ->rules(['required', 'max:50']),
        ];
    }

    public function resolveRecord(): CatalogoObraComplementaria
    {
        // Si el código '01.01' ya existe, lo actualiza. Si no, lo crea.
        return CatalogoObraComplementaria::firstOrNew([
            'codigo' => $this->data['codigo'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your catalogo obra complementaria import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
