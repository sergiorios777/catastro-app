<?php

namespace App\Filament\Resources\Depreciacions\Pages;

use App\Filament\Resources\Depreciacions\DepreciacionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDepreciacions extends ListRecords
{
    protected static string $resource = DepreciacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
