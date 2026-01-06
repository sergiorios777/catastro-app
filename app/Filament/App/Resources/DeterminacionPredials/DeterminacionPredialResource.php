<?php

namespace App\Filament\App\Resources\DeterminacionPredials;

use App\Filament\App\Resources\DeterminacionPredials\Pages\CreateDeterminacionPredial;
use App\Filament\App\Resources\DeterminacionPredials\Pages\EditDeterminacionPredial;
use App\Filament\App\Resources\DeterminacionPredials\Pages\ListDeterminacionPredials;
use App\Filament\App\Resources\DeterminacionPredials\Schemas\DeterminacionPredialForm;
use App\Filament\App\Resources\DeterminacionPredials\Tables\DeterminacionPredialsTable;
use App\Models\DeterminacionPredial;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeterminacionPredialResource extends Resource
{
    protected static ?string $model = DeterminacionPredial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Deudas';

    public static function form(Schema $schema): Schema
    {
        return DeterminacionPredialForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeterminacionPredialsTable::configure($table);
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
            'index' => ListDeterminacionPredials::route('/'),
            //'create' => CreateDeterminacionPredial::route('/create'),
            //'edit' => EditDeterminacionPredial::route('/{record}/edit'),
        ];
    }
}
