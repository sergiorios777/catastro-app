<?php

namespace App\Filament\Imports;

use App\Models\ArancelUrbano;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ArancelUrbanoImporter extends Importer
{
    protected static ?string $model = ArancelUrbano::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('anioFiscal')
                ->relationship(resolveUsing: 'anio') // Referencia visual
                ->rules(['required']),

            ImportColumn::make('ubigeo_distrito')
                ->label('Ubigeo (6 dígitos)')
                ->rules(['required', 'string', 'size:6']),

            // La Matriz de Características
            ImportColumn::make('tipo_calzada')
                ->rules(['required', 'in:tierra,afirmado,empedrado,asfalto,concreto']),

            ImportColumn::make('ancho_via')
                ->rules(['required', 'in:menos_6,entre_6_8,mas_8']),

            // Booleans: Esperamos 1 o 0 en el CSV
            ImportColumn::make('tiene_agua')->boolean()->rules(['required', 'boolean']),
            ImportColumn::make('tiene_desague')->boolean()->rules(['required', 'boolean']),
            ImportColumn::make('tiene_luz')->boolean()->rules(['required', 'boolean']),

            ImportColumn::make('valor_arancel')
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),
        ];
    }

    public function resolveRecord(): ?ArancelUrbano
    {
        // dd($this->data);
        // 1. Búsqueda Manual del Año (Anti-errores)
        $anioId = \App\Models\AnioFiscal::where('anio', $this->data['anioFiscal'])->value('id');

        if (!$anioId)
            return null;

        // 2. Crear o Actualizar
        return ArancelUrbano::firstOrNew([
            'anio_fiscal_id' => $anioId,
            'ubigeo_distrito' => $this->data['ubigeo_distrito'],
            'tipo_calzada' => $this->data['tipo_calzada'],
            'ancho_via' => $this->data['ancho_via'],
            'tiene_agua' => (bool) $this->data['tiene_agua'],
            'tiene_desague' => (bool) $this->data['tiene_desague'],
            'tiene_luz' => (bool) $this->data['tiene_luz'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your arancel urbano import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
