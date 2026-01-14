<?php

namespace App\Filament\Resources\ValorUnitarioEdificacions;

use App\Filament\Resources\ValorUnitarioEdificacions\Pages\CreateValorUnitarioEdificacion;
use App\Filament\Resources\ValorUnitarioEdificacions\Pages\EditValorUnitarioEdificacion;
use App\Filament\Resources\ValorUnitarioEdificacions\Pages\ListValorUnitarioEdificacions;
use App\Filament\Resources\ValorUnitarioEdificacions\Schemas\ValorUnitarioEdificacionForm;
use App\Filament\Resources\ValorUnitarioEdificacions\Tables\ValorUnitarioEdificacionsTable;
use App\Models\ValorUnitarioEdificacion;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ValorUnitarioEdificacionResource extends Resource
{
    protected static ?string $model = ValorUnitarioEdificacion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Valor unitario';
    protected static ?string $navigationLabel = 'Valores unitarios de edificaciones';
    protected static string|UnitEnum|null $navigationGroup = 'Valores Oficiales Edificación';

    public static function form(Schema $schema): Schema
    {
        return ValorUnitarioEdificacionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ValorUnitarioEdificacionsTable::configure($table);
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
            'index' => ListValorUnitarioEdificacions::route('/'),
            'create' => CreateValorUnitarioEdificacion::route('/create'),
            'edit' => EditValorUnitarioEdificacion::route('/{record}/edit'),
        ];
    }
}
