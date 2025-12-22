<?php

namespace App\Filament\App\Resources\DeterminacionPredials\Pages;

use App\Filament\App\Resources\DeterminacionPredials\DeterminacionPredialResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDeterminacionPredial extends EditRecord
{
    protected static string $resource = DeterminacionPredialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
