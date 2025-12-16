<?php

namespace App\Filament\App\Resources\AnioFiscals\Pages;

use App\Filament\App\Resources\AnioFiscals\AnioFiscalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAnioFiscal extends EditRecord
{
    protected static string $resource = AnioFiscalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
