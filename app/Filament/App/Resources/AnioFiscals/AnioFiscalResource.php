<?php

namespace App\Filament\App\Resources\AnioFiscals;

use App\Filament\App\Resources\AnioFiscals\Pages\CreateAnioFiscal;
use App\Filament\App\Resources\AnioFiscals\Pages\EditAnioFiscal;
use App\Filament\App\Resources\AnioFiscals\Pages\ListAnioFiscals;
use App\Filament\App\Resources\AnioFiscals\Schemas\AnioFiscalForm;
use App\Filament\App\Resources\AnioFiscals\Tables\AnioFiscalsTable;
use App\Models\AnioFiscal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AnioFiscalResource extends Resource
{
    protected static ?string $model = AnioFiscal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'anio';

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
