<?php

namespace App\Filament\Imports;

use App\Models\ValorUnitarioEdificacion;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ValorUnitarioEdificacionImporter extends Importer
{
    protected static ?string $model = ValorUnitarioEdificacion::class;

    public static function getColumns(): array
    {
        return [
            // 1. Relación con Año Fiscal
            ImportColumn::make('anioFiscal')
                ->relationship(resolveUsing: 'anio'),

            // 2. Enums
            ImportColumn::make('zona_geografica')
                ->requiredMapping()
                ->rules(['required', 'string']),

            ImportColumn::make('componente')
                ->requiredMapping()
                ->rules(['required', 'string']),

            ImportColumn::make('categoria')
                ->requiredMapping()
                ->rules(['required', 'max:1']),

            ImportColumn::make('valor')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),
        ];
    }

    // --- BORRÉ LA FUNCIÓN QUE BUSCABA POR 'ID' QUE ESTABA AQUÍ ---

    // ESTA ES LA ÚNICA DEFINICIÓN QUE DEBE QUEDAR:
    public function resolveRecord(): ?ValorUnitarioEdificacion
    {
        // Usamos firstOrNew para que:
        // SI existe la combinación Año+Zona+Componente+Categoria -> Lo actualice (Update)
        // SI NO existe -> Lo cree (Create)
        return ValorUnitarioEdificacion::firstOrNew([
            'anio_fiscal_id' => $this->data['anioFiscal'], // Nota: Filament suele mapear la relación al nombre de la columna definida
            'zona_geografica' => $this->data['zona_geografica'],
            'componente' => $this->data['componente'],
            'categoria' => $this->data['categoria'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Importación de valores unitarios completada y ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' importadas.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' fallaron al importar.';
        }

        return $body;
    }
}
