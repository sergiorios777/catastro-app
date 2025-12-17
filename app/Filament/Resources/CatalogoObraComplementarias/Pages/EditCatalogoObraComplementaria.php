<?php

namespace App\Filament\Resources\CatalogoObraComplementarias\Pages;

use App\Filament\Resources\CatalogoObraComplementarias\CatalogoObraComplementariaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCatalogoObraComplementaria extends EditRecord
{
    protected static string $resource = CatalogoObraComplementariaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
