<?php

namespace App\Filament\Resources\CatalogoObraComplementarias;

use App\Filament\Resources\CatalogoObraComplementarias\Pages\CreateCatalogoObraComplementaria;
use App\Filament\Resources\CatalogoObraComplementarias\Pages\EditCatalogoObraComplementaria;
use App\Filament\Resources\CatalogoObraComplementarias\Pages\ListCatalogoObraComplementarias;
use App\Filament\Resources\CatalogoObraComplementarias\Schemas\CatalogoObraComplementariaForm;
use App\Filament\Resources\CatalogoObraComplementarias\Tables\CatalogoObraComplementariasTable;
use App\Models\CatalogoObraComplementaria;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CatalogoObraComplementariaResource extends Resource
{
    protected static ?string $model = CatalogoObraComplementaria::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Catalogo obra';
    protected static ?string $navigationLabel = 'Catalogo de obras complementarias';
    protected static string|UnitEnum|null $navigationGroup = 'Valores Oficiales Edificación';

    public static function form(Schema $schema): Schema
    {
        return CatalogoObraComplementariaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CatalogoObraComplementariasTable::configure($table);
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
            'index' => ListCatalogoObraComplementarias::route('/'),
            'create' => CreateCatalogoObraComplementaria::route('/create'),
            'edit' => EditCatalogoObraComplementaria::route('/{record}/edit'),
        ];
    }
}
