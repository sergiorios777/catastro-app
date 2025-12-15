<?php

namespace App\Filament\App\Resources\PredioFisicos\Pages;

use App\Filament\App\Resources\PredioFisicos\PredioFisicoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPredioFisicos extends ListRecords
{
    protected static string $resource = PredioFisicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
