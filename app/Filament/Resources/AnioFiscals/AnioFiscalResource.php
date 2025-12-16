<?php

namespace App\Filament\Resources\AnioFiscals;

use App\Filament\Resources\AnioFiscals\Pages\CreateAnioFiscal;
use App\Filament\Resources\AnioFiscals\Pages\EditAnioFiscal;
use App\Filament\Resources\AnioFiscals\Pages\ListAnioFiscals;
use App\Filament\Resources\AnioFiscals\Schemas\AnioFiscalForm;
use App\Filament\Resources\AnioFiscals\Tables\AnioFiscalsTable;
use App\Models\AnioFiscal;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AnioFiscalResource extends Resource
{
    protected static ?string $model = AnioFiscal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'anio';
    protected static ?string $navigationLabel = 'Años fiscales';
    protected static string|UnitEnum|null $navigationGroup = 'Parámetros globales';

    public static function form(Schema $schema): Schema
    {
        return AnioFiscalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AnioFiscalsTable::configure($table);
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
            'index' => ListAnioFiscals::route('/'),
            'create' => CreateAnioFiscal::route('/create'),
            'edit' => EditAnioFiscal::route('/{record}/edit'),
        ];
    }
}
