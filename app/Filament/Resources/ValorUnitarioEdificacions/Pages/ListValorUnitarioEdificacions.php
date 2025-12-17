<?php

namespace App\Filament\Resources\ValorUnitarioEdificacions\Pages;

use App\Filament\Resources\ValorUnitarioEdificacions\ValorUnitarioEdificacionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListValorUnitarioEdificacions extends ListRecords
{
    protected static string $resource = ValorUnitarioEdificacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
