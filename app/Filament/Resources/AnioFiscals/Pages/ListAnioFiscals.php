<?php

namespace App\Filament\Resources\AnioFiscals\Pages;

use App\Filament\Resources\AnioFiscals\AnioFiscalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAnioFiscals extends ListRecords
{
    protected static string $resource = AnioFiscalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
