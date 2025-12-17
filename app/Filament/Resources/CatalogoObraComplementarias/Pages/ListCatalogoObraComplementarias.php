<?php

namespace App\Filament\Resources\CatalogoObraComplementarias\Pages;

use App\Filament\Resources\CatalogoObraComplementarias\CatalogoObraComplementariaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCatalogoObraComplementarias extends ListRecords
{
    protected static string $resource = CatalogoObraComplementariaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
