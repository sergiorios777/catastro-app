<?php

namespace App\Filament\Resources\ArancelRusticos\Pages;

use App\Filament\Resources\ArancelRusticos\ArancelRusticoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditArancelRustico extends EditRecord
{
    protected static string $resource = ArancelRusticoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
