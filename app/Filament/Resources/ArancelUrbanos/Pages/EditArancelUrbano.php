<?php

namespace App\Filament\Resources\ArancelUrbanos\Pages;

use App\Filament\Resources\ArancelUrbanos\ArancelUrbanoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditArancelUrbano extends EditRecord
{
    protected static string $resource = ArancelUrbanoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
