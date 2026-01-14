<?php

namespace App\Filament\Resources\ArancelRusticos;

use App\Filament\Resources\ArancelRusticos\Pages\CreateArancelRustico;
use App\Filament\Resources\ArancelRusticos\Pages\EditArancelRustico;
use App\Filament\Resources\ArancelRusticos\Pages\ListArancelRusticos;
use App\Filament\Resources\ArancelRusticos\Schemas\ArancelRusticoForm;
use App\Filament\Resources\ArancelRusticos\Tables\ArancelRusticosTable;
use App\Models\ArancelRustico;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ArancelRusticoResource extends Resource
{
    protected static ?string $model = ArancelRustico::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Arancel T. Rústicos';
    protected static string|UnitEnum|null $navigationGroup = 'Valores Arancelarios';

    public static function form(Schema $schema): Schema
    {
        return ArancelRusticoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArancelRusticosTable::configure($table);
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
            'index' => ListArancelRusticos::route('/'),
            'create' => CreateArancelRustico::route('/create'),
            'edit' => EditArancelRustico::route('/{record}/edit'),
        ];
    }
}
