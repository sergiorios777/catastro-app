<?php

namespace App\Filament\App\Resources\PredioFisicos\Pages;

use App\Filament\App\Resources\PredioFisicos\PredioFisicoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPredioFisico extends EditRecord
{
    protected static string $resource = PredioFisicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
