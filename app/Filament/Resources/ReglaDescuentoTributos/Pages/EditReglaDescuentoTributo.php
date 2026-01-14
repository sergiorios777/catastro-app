<?php

namespace App\Filament\Resources\ReglaDescuentoTributos\Pages;

use App\Filament\Resources\ReglaDescuentoTributos\ReglaDescuentoTributoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReglaDescuentoTributo extends EditRecord
{
    protected static string $resource = ReglaDescuentoTributoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
