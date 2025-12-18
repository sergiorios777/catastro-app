<?php

namespace App\Filament\Resources\ArancelUrbanos\Pages;

use App\Filament\Resources\ArancelUrbanos\ArancelUrbanoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArancelUrbanos extends ListRecords
{
    protected static string $resource = ArancelUrbanoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
