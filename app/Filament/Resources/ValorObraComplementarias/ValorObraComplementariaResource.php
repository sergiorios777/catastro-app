<?php

namespace App\Filament\Resources\ValorObraComplementarias;

use App\Filament\Resources\ValorObraComplementarias\Pages\CreateValorObraComplementaria;
use App\Filament\Resources\ValorObraComplementarias\Pages\EditValorObraComplementaria;
use App\Filament\Resources\ValorObraComplementarias\Pages\ListValorObraComplementarias;
use App\Filament\Resources\ValorObraComplementarias\Schemas\ValorObraComplementariaForm;
use App\Filament\Resources\ValorObraComplementarias\Tables\ValorObraComplementariasTable;
use App\Models\ValorObraComplementaria;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ValorObraComplementariaResource extends Resource
{
    protected static ?string $model = ValorObraComplementaria::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Valor obra';

    public static function form(Schema $schema): Schema
    {
        return ValorObraComplementariaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ValorObraComplementariasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListValorObraComplementarias::route('/'),
            'create' => CreateValorObraComplementaria::route('/create'),
            'edit' => EditValorObraComplementaria::route('/{record}/edit'),
        ];
    }
}
