<?php

namespace App\Filament\Resources\ArancelUrbanos;

use App\Filament\Resources\ArancelUrbanos\Pages\CreateArancelUrbano;
use App\Filament\Resources\ArancelUrbanos\Pages\EditArancelUrbano;
use App\Filament\Resources\ArancelUrbanos\Pages\ListArancelUrbanos;
use App\Filament\Resources\ArancelUrbanos\Schemas\ArancelUrbanoForm;
use App\Filament\Resources\ArancelUrbanos\Tables\ArancelUrbanosTable;
use App\Models\ArancelUrbano;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ArancelUrbanoResource extends Resource
{
    protected static ?string $model = ArancelUrbano::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Arancel T. Urbanos';

    public static function form(Schema $schema): Schema
    {
        return ArancelUrbanoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArancelUrbanosTable::configure($table);
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
            'index' => ListArancelUrbanos::route('/'),
            'create' => CreateArancelUrbano::route('/create'),
            'edit' => EditArancelUrbano::route('/{record}/edit'),
        ];
    }
}
