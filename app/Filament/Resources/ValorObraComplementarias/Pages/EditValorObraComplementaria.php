<?php

namespace App\Filament\Resources\ValorObraComplementarias\Pages;

use App\Filament\Resources\ValorObraComplementarias\ValorObraComplementariaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditValorObraComplementaria extends EditRecord
{
    protected static string $resource = ValorObraComplementariaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
