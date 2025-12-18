<?php

namespace App\Filament\Resources\Depreciacions\Pages;

use App\Filament\Resources\Depreciacions\DepreciacionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDepreciacion extends EditRecord
{
    protected static string $resource = DepreciacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
