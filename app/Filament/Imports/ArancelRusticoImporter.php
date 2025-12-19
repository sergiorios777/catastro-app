<?php

namespace App\Filament\Imports;

use App\Models\ArancelRustico;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ArancelRusticoImporter extends Importer
{
    protected static ?string $model = ArancelRustico::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('anioFiscal')->relationship(resolveUsing: 'anio'),

            ImportColumn::make('ubigeo_provincia')
                ->label('Ubigeo Prov (4 dígitos)')
                ->rules(['required', 'size:4']),

            ImportColumn::make('grupo_tierras')
                ->rules(['required', 'in:A,C,P,X']),

            // Estos pueden venir vacíos en el CSV si es Eriazo
            ImportColumn::make('distancia'),
            ImportColumn::make('calidad_agrologica'),

            ImportColumn::make('valor_arancel')
                ->numeric()
                ->rules(['required', 'numeric']),
        ];
    }

    public function resolveRecord(): ?ArancelRustico
    {
        $anioId = \App\Models\AnioFiscal::where('anio', $this->data['anioFiscal'])->value('id');

        if (!$anioId)
            return null;

        return ArancelRustico::firstOrNew([
            'anio_fiscal_id' => $anioId,
            'ubigeo_provincia' => $this->data['ubigeo_provincia'],
            'grupo_tierras' => $this->data['grupo_tierras'],
            // Usamos '?? null' por si el CSV trae la celda vacía
            'distancia' => $this->data['distancia'] ?? null,
            'calidad_agrologica' => $this->data['calidad_agrologica'] ?? null,
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your arancel rustico import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
