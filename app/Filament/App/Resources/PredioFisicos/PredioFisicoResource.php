<?php

namespace App\Filament\App\Resources\PredioFisicos;

use App\Filament\App\Resources\PredioFisicos\Pages\CreatePredioFisico;
use App\Filament\App\Resources\PredioFisicos\Pages\EditPredioFisico;
use App\Filament\App\Resources\PredioFisicos\Pages\ListPredioFisicos;
use App\Filament\App\Resources\PredioFisicos\Schemas\PredioFisicoForm;
use App\Filament\App\Resources\PredioFisicos\Tables\PredioFisicosTable;
use App\Models\PredioFisico;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PredioFisicoResource extends Resource
{
    protected static ?string $model = PredioFisico::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Predios Físicos';
    protected static ?string $modelLabel = 'Predio Físico';
    protected static ?string $pluralModelLabel = 'Predios Físicos';

    protected static ?string $recordTitleAttribute = 'cuc';

    public static function form(Schema $schema): Schema
    {
        return PredioFisicoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PredioFisicosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PropietariosRelationManager::class,
            RelationManagers\ConstruccionesRelationManager::class,
            RelationManagers\ObrasComplementariasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPredioFisicos::route('/'),
            'create' => CreatePredioFisico::route('/create'),
            'edit' => EditPredioFisico::route('/{record}/edit'),
        ];
    }
}
