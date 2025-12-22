<?php

namespace App\Filament\App\Resources\DeterminacionPredials\Pages;

use App\Filament\App\Resources\DeterminacionPredials\DeterminacionPredialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeterminacionPredials extends ListRecords
{
    protected static string $resource = DeterminacionPredialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
