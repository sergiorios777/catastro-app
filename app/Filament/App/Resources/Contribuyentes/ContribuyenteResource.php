<?php

namespace App\Filament\App\Resources\Contribuyentes;

use App\Filament\App\Resources\Contribuyentes\Pages\CreateContribuyente;
use App\Filament\App\Resources\Contribuyentes\Pages\EditContribuyente;
use App\Filament\App\Resources\Contribuyentes\Pages\ListContribuyentes;
use App\Filament\App\Resources\Contribuyentes\Schemas\ContribuyenteForm;
use App\Filament\App\Resources\Contribuyentes\Tables\ContribuyentesTable;
use App\Models\Persona;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContribuyenteResource extends Resource
{
    protected static ?string $model = Persona::class;

    protected static ?string $slug = 'contribuyentes';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'contribuyente';
    protected static ?string $navigationLabel = 'Contribuyentes';

    protected static ?string $modelLabel = 'Contribuyente';
    protected static ?string $pluralModelLabel = 'Contribuyentes';

    public static function form(Schema $schema): Schema
    {
        return ContribuyenteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContribuyentesTable::configure($table);
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
            'index' => ListContribuyentes::route('/'),
            // 'create' => CreateContribuyente::route('/create'),
            // 'edit' => EditContribuyente::route('/{record}/edit'),
        ];
    }
}
