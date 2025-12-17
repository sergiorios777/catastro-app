<?php

namespace App\Filament\Resources\ValorObraComplementarias\Pages;

use App\Filament\Resources\ValorObraComplementarias\ValorObraComplementariaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListValorObraComplementarias extends ListRecords
{
    protected static string $resource = ValorObraComplementariaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
