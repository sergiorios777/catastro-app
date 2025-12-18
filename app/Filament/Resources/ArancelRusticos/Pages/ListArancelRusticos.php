<?php

namespace App\Filament\Resources\ArancelRusticos\Pages;

use App\Filament\Resources\ArancelRusticos\ArancelRusticoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArancelRusticos extends ListRecords
{
    protected static string $resource = ArancelRusticoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
