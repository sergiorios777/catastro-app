<?php

namespace App\Filament\Resources\Depreciacions;

use App\Filament\Resources\Depreciacions\Pages\CreateDepreciacion;
use App\Filament\Resources\Depreciacions\Pages\EditDepreciacion;
use App\Filament\Resources\Depreciacions\Pages\ListDepreciacions;
use App\Filament\Resources\Depreciacions\Schemas\DepreciacionForm;
use App\Filament\Resources\Depreciacions\Tables\DepreciacionsTable;
use App\Models\Depreciacion;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DepreciacionResource extends Resource
{
    protected static ?string $model = Depreciacion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationLabel = 'Tabla de Depreciación';
    protected static ?string $modelLabel = 'Tabla de Depreciaciones';
    protected static string|UnitEnum|null $navigationGroup = 'Valores Oficiales Edificación';

    public static function form(Schema $schema): Schema
    {
        return DepreciacionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepreciacionsTable::configure($table);
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
            'index' => ListDepreciacions::route('/'),
            'create' => CreateDepreciacion::route('/create'),
            'edit' => EditDepreciacion::route('/{record}/edit'),
        ];
    }
}
