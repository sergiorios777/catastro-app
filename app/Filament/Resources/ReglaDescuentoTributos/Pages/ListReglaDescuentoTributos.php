<?php

namespace App\Filament\Resources\ReglaDescuentoTributos\Pages;

use App\Filament\Resources\ReglaDescuentoTributos\ReglaDescuentoTributoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReglaDescuentoTributos extends ListRecords
{
    protected static string $resource = ReglaDescuentoTributoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
