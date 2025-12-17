<?php

namespace App\Filament\Resources\ValorUnitarioEdificacions\Pages;

use App\Filament\Resources\ValorUnitarioEdificacions\ValorUnitarioEdificacionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditValorUnitarioEdificacion extends EditRecord
{
    protected static string $resource = ValorUnitarioEdificacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
