<?php

namespace App\Filament\Resources\ReglaDescuentoTributos;

use App\Filament\Resources\ReglaDescuentoTributos\Pages\CreateReglaDescuentoTributo;
use App\Filament\Resources\ReglaDescuentoTributos\Pages\EditReglaDescuentoTributo;
use App\Filament\Resources\ReglaDescuentoTributos\Pages\ListReglaDescuentoTributos;
use App\Filament\Resources\ReglaDescuentoTributos\Schemas\ReglaDescuentoTributoForm;
use App\Filament\Resources\ReglaDescuentoTributos\Tables\ReglaDescuentoTributosTable;
use App\Models\ReglasDescuentoTributo;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReglaDescuentoTributoResource extends Resource
{
    protected static ?string $model = ReglasDescuentoTributo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'codigo';
    protected static ?string $navigationLabel = 'Reglas y Beneficios';
    protected static ?string $modelLabel = 'Norma Tributaria';
    protected static string|UnitEnum|null $navigationGroup = 'Configuración Global';

    public static function form(Schema $schema): Schema
    {
        return ReglaDescuentoTributoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReglaDescuentoTributosTable::configure($table);
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
            'index' => ListReglaDescuentoTributos::route('/'),
            'create' => CreateReglaDescuentoTributo::route('/create'),
            'edit' => EditReglaDescuentoTributo::route('/{record}/edit'),
        ];
    }
}
