<?php

namespace App\Filament\App\Resources\Contribuyentes\Pages;

use App\Filament\App\Resources\Contribuyentes\ContribuyenteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContribuyente extends EditRecord
{
    protected static string $resource = ContribuyenteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
