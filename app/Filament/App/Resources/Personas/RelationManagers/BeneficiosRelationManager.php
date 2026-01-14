<?php

namespace App\Filament\App\Resources\Personas\RelationManagers;

use App\Models\ReglasDescuentoTributo;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use BackedEnum;

class BeneficiosRelationManager extends RelationManager
{
    protected static string $relationship = 'beneficios';
    protected static ?string $title = 'Beneficios Tributarios (Pensionistas/Otros)';
    protected static string|BackedEnum|null $icon = 'heroicon-o-identification';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)->schema([
                    Select::make('regla_descuento_tributo_id') // Tu campo FK exacto
                        ->label('Tipo de Beneficio')
                        ->options(function () {
                            // Filtramos solo reglas de tipo 'deduccion' (Personas)
                            // Ajusta la clase del modelo si usaste ReglasDescuentoTributo
                            return ReglasDescuentoTributo::where('tipo_beneficio', 'deduccion')
                                ->where('is_active', true)
                                ->pluck('nombre', 'id');
                        })
                        ->required()
                        ->searchable()
                        ->preload(),

                    TextInput::make('documento_resolucion')
                        ->label('Documento de Sustento')
                        ->placeholder('Ej: Resolución Gerencial N° 123-2025')
                        ->required()
                        ->maxLength(255),

                    Grid::make(2)->schema([
                        DatePicker::make('valid_from')
                            ->label('Vigente Desde')
                            ->default(now())
                            ->required(),

                        DatePicker::make('valid_to')
                            ->label('Vigente Hasta')
                            ->placeholder('Indefinido')
                            ->helperText('Dejar vacío si es permanente'),
                    ]),

                    Textarea::make('observacion')
                        ->label('Observaciones')
                        ->rows(2)
                        ->columnSpanFull(),

                    Toggle::make('is_active')
                        ->label('Beneficio Activo')
                        ->default(true),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('documento_resolucion')
            ->columns([
                // Usamos la relación definida en tu modelo BeneficioPersona
                TextColumn::make('reglaDescuentoTributo.nombre')
                    ->label('Beneficio')
                    ->weight('bold')
                    ->icon('heroicon-m-shield-check')
                    ->color('primary'),

                TextColumn::make('documento_resolucion')
                    ->label('Documento')
                    ->searchable(),

                TextColumn::make('valid_from')
                    ->label('Desde')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('valid_to')
                    ->label('Hasta')
                    ->date('d/m/Y')
                    ->placeholder('Indefinido'),

                ToggleColumn::make('is_active')
                    ->label('Activo'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
