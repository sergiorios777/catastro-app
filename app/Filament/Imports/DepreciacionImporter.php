<?php

namespace App\Filament\Imports;

use App\Models\Depreciacion;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class DepreciacionImporter extends Importer
{
    protected static ?string $model = Depreciacion::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('material')
                ->rules(['required', 'in:concreto,ladrillo,adobe,madera']),

            ImportColumn::make('uso')
                ->label('Uso (Clasificación)')
                ->rules(['required', 'in:casa_habitacion,tienda_deposito,edificio_oficina,industria_salud,otros']),

            ImportColumn::make('estado_conservacion')
                ->rules(['required', 'in:muy_bueno,bueno,regular,malo']),

            ImportColumn::make('antiguedad')
                ->numeric()
                ->rules(['required', 'integer', 'min:0']),

            ImportColumn::make('porcentaje')
                ->numeric()
                ->rules(['required', 'numeric', 'min:0', 'max:100']),
        ];
    }

    public function resolveRecord(): Depreciacion
    {
        return Depreciacion::firstOrNew([
            'material' => $this->data['material'],
            'uso' => $this->data['uso'],
            'estado_conservacion' => $this->data['estado_conservacion'],
            'antiguedad' => $this->data['antiguedad'],
        ], [
            'porcentaje' => $this->data['porcentaje']
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your depreciacion import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
