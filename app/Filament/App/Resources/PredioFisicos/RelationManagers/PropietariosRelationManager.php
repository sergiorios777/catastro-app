<?php

namespace App\Filament\App\Resources\PredioFisicos\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Closure;
use App\Filament\App\Resources\Personas\Schemas\PersonaForm;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class PropietariosRelationManager extends RelationManager
{
    // ANTES: protected static string $relationship = 'propietariosActuales';
    // AHORA: protected static string $relationship = 'propietarios';
    protected static string $relationship = 'propietarios';
    protected static ?string $title = 'Titularidad / Propietarios';

    /**
     * IMPORTANTE: El método form() define cómo se crea/edita el registro RELACIONADO (La Persona).
     * Aquí NO debemos poner campos de la tabla intermedia (como porcentaje).
     */
    public function form(Schema $schema): Schema
    {
        // Reutilizamos el esquema que creamos en PersonaResource para no duplicar código
        return PersonaForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero_documento')
            ->columns([
                TextColumn::make('nombre_completo')
                    ->label('Propietario'),

                TextColumn::make('pivot.porcentaje_propiedad') // Accedemos a la tabla pivote
                    ->label('% Propiedad')
                    ->suffix('%')
                    ->alignEnd(),

                TextColumn::make('pivot.tipo_propiedad')
                    ->label('Condición')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => ucfirst(str_replace('_', ' ', $state))),

                TextColumn::make('pivot.vigente')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? 'VIGENTE' : 'HISTÓRICO')
                    ->color(fn(bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->headerActions([
                // 1. ACCIÓN PARA VINCULAR ALGUIEN YA EXISTENTE
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->label('Agregar Existente')
                    ->modalHeading('Vincular Propietario Existente')
                    ->recordSelectSearchColumns(['numero_documento', 'nombres', 'apellidos', 'razon_social'])
                    // --- NUEVO: PERSONALIZAMOS LA ETIQUETA DEL SELECTOR ---
                    ->recordTitle(fn($record) => "{$record->numero_documento} - " . ($record->razon_social ?: "{$record->nombres} {$record->apellidos}"))
                    // -----------------------------------------------------
                    ->form(fn(AttachAction $action): array => [
                        $action->getRecordSelect(),

                        // Campos de la Tabla Pivote (Intermedia)
                        TextInput::make('porcentaje_propiedad')
                            ->label('% Participación')
                            ->numeric()
                            ->default(100)
                            ->suffix('%')
                            ->required()
                            // --- INICIO VALIDACIÓN ---
                            ->rules([
                                // CORRECCIÓN: Inyectamos el RelationManager ($livewire) directamente
                                fn(PropietariosRelationManager $livewire) => function (string $attribute, $value, Closure $fail) use ($livewire) {
                                    // 1. Obtenemos el Predio directamente del Manager
                                    $predio = $livewire->getOwnerRecord();

                                    // 2. Sumamos todo lo vigente
                                    $sumaActual = DB::table('propietario_predios')
                                        ->where('predio_fisico_id', $predio->id)
                                        ->where('vigente', true)
                                        ->sum('porcentaje_propiedad');

                                    // 3. Validamos
                                    if (($sumaActual + $value) > 100) {
                                        $disponible = 100 - $sumaActual;
                                        $fail("El total superaría el 100%. Solo queda disponible: {$disponible}%");
                                    }
                                },
                            ]),
                        // --- FIN VALIDACIÓN ---

                        Select::make('tipo_propiedad')
                            ->options([
                                'unico' => 'Único Propietario',
                                'copropiedad' => 'Copropiedad',
                                'sociedad_conyugal' => 'Sociedad Conyugal',
                                'sucesion' => 'Sucesión Indivisa',
                            ])
                            ->default('unico')
                            ->required(),

                        DatePicker::make('fecha_inicio')
                            ->default(now()),

                        TextInput::make('documento_sustento'),
                    ]),

                // 2. ACCIÓN AVANZADA: CREAR PERSONA Y VINCULAR CON DATOS ESPECÍFICOS
                CreateAction::make()
                    ->label('Crear Nuevo')
                    ->modalHeading('Registrar Nuevo Propietario')
                    ->modalWidth('2xl')
                    // 1. FUSIONAMOS LOS FORMULARIOS
                    ->form(fn(CreateAction $action) => [
                        // A. Datos de la Persona (Traídos de tu esquema original)
                        Group::make()
                            ->schema(fn(Schema $schema) => PersonaForm::configure($schema))
                            ->columnSpanFull(),

                        // B. Datos de la Propiedad (Los campos de la tabla pivote)
                        Section::make('Condiciones de Propiedad')
                            ->schema([
                                TextInput::make('porcentaje_propiedad')
                                    ->label('% Participación')
                                    ->numeric()
                                    ->default(100)
                                    ->suffix('%')
                                    ->required()
                                    // --- INICIO VALIDACIÓN ---
                                    ->rules([
                                        // CORRECCIÓN AQUÍ TAMBIÉN
                                        fn(Get $get, PropietariosRelationManager $livewire) => function (string $attribute, $value, Closure $fail) use ($get, $livewire) {

                                            if (!$get('vigente'))
                                                return;

                                            // Usamos $livewire en lugar de $action
                                            $predio = $livewire->getOwnerRecord();

                                            $sumaActual = DB::table('propietario_predios')
                                                ->where('predio_fisico_id', $predio->id)
                                                ->where('vigente', true)
                                                ->sum('porcentaje_propiedad');

                                            if (($sumaActual + $value) > 100) {
                                                $disponible = 100 - $sumaActual;
                                                $fail("El total superaría el 100%. Solo queda disponible: {$disponible}%");
                                            }
                                        },
                                    ]),
                                // --- FIN VALIDACIÓN ---

                                Select::make('tipo_propiedad')
                                    ->options([
                                        'unico' => 'Único Propietario',
                                        'copropiedad' => 'Copropiedad',
                                        'sociedad_conyugal' => 'Sociedad Conyugal',
                                        'sucesion' => 'Sucesión Indivisa',
                                    ])
                                    ->default('unico')
                                    ->required(),

                                DatePicker::make('fecha_inicio')
                                    ->label('Fecha Adquisición')
                                    ->default(now()),

                                TextInput::make('documento_sustento')
                                    ->label('Doc. Sustento'),

                                // Agregamos el check de vigente por si acaso
                                Toggle::make('vigente')
                                    ->label('Vigente')
                                    ->default(true),
                            ])
                            ->columns(2),
                    ])
                    // 2. INTERCEPTAMOS EL GUARDADO (LA LÓGICA MÁGICA)
                    ->action(function (array $data, \Livewire\Component $livewire) {
                        // Paso A: Separar los datos
                        // Identificamos cuáles campos son del Pivot
                        $pivotFields = [
                            'porcentaje_propiedad',
                            'tipo_propiedad',
                            'fecha_inicio',
                            'documento_sustento',
                            'vigente'
                        ];

                        // Extraemos los datos para el Pivot
                        $pivotData = \Illuminate\Support\Arr::only($data, $pivotFields);

                        // Extraemos los datos para la Persona (todo lo demás)
                        $personaData = \Illuminate\Support\Arr::except($data, $pivotFields);

                        // Paso B: Crear la Persona
                        // Usamos el modelo Persona directamente
                        $persona = \App\Models\Persona::create($personaData);

                        // Paso C: Vincular (Attach) con los datos personalizados
                        // $livewire->getOwnerRecord() es el Predio Fisico actual
                        $livewire->getOwnerRecord()->propietarios()->attach($persona->id, $pivotData);

                        // Paso D: Notificar éxito
                        Notification::make()
                            ->title('Propietario registrado y vinculado')
                            ->success()
                            ->send();
                    }),
            ])
            ->filters([
                // Filtro para alternar entre Vigentes e Históricos
                Tables\Filters\SelectFilter::make('vigente')
                    ->label('Estado de Propiedad')
                    ->options([
                        '1' => 'Vigentes (Actuales)',
                        '0' => 'Históricos (Anteriores)',
                    ])
                    ->attribute('propietario_predios.vigente') // Especificamos la tabla pivote
                    ->default('1'), // Por defecto, mostramos solo los actuales para no asustar
            ])
            ->recordActions([
                // 1. EDIT ACTION (NATIVO ADAPTADO A PIVOTE)
                EditAction::make()
                    ->label('Editar Condiciones')
                    ->modalWidth('md')
                    ->form([
                        TextInput::make('porcentaje_propiedad')
                            ->label('% Participación')
                            ->numeric()
                            ->default(100)
                            ->suffix('%')
                            ->required()
                            // --- INICIO VALIDACIÓN ---
                            ->rules([
                                fn(Model $record, Get $get) => function (string $attribute, $value, Closure $fail) use ($record, $get) {
                                    // 1. Si no es vigente, no validamos suma (permitimos histórico)
                                    if (!$get('vigente'))
                                        return;

                                    // 2. Obtenemos el ID del Predio (desde la relación inversa del registro pivote)
                                    $predioId = $record->pivot->predio_fisico_id;
                                    $personaId = $record->pivot->persona_id; // ID actual para excluirlo de la suma
                        
                                    // 3. Sumamos TODO lo demás que esté vigente en la BD
                                    $sumaActual = DB::table('propietario_predios')
                                        ->where('predio_fisico_id', $predioId)
                                        ->where('vigente', true)
                                        ->where('persona_id', '!=', $personaId) // ¡OJO! Excluimos al que estamos editando
                                        ->sum('porcentaje_propiedad');

                                    // 4. Verificamos si nos pasamos
                                    if (($sumaActual + $value) > 100) {
                                        $disponible = 100 - $sumaActual;
                                        $fail("El total superaría el 100%. Solo queda disponible: {$disponible}%");
                                    }
                                },
                            ]),
                        // --- FIN VALIDACIÓN ---

                        Select::make('tipo_propiedad')
                            ->options([
                                'unico' => 'Único Propietario',
                                'copropiedad' => 'Copropiedad',
                                'sociedad_conyugal' => 'Sociedad Conyugal',
                                'sucesion' => 'Sucesión Indivisa',
                            ])
                            ->required(),

                        DatePicker::make('fecha_inicio')
                            ->label('Fecha Adquisición'),

                        // --- AQUÍ AGREGAMOS LA FECHA DE FIN (OPCIONAL) ---
                        DatePicker::make('fecha_fin')
                            ->label('Fecha de Baja')
                            ->helperText('Llenar solo si deja de ser propietario'),

                        TextInput::make('documento_sustento')
                            ->label('Doc. Sustento'),

                        // --- AQUÍ AGREGAMOS EL CONTROL DE VIGENCIA ---
                        Section::make()
                            ->schema([
                                Toggle::make('vigente')
                                    ->label('Es Propietario Vigente (Actual)')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->default(true)
                                    ->helperText('Desactive para moverlo al historial'),
                            ]),
                    ])
                    // B. FILL: ¡No olvides mapear el campo aquí también!
                    ->fillForm(fn($record): array => [
                        'porcentaje_propiedad' => $record->pivot->porcentaje_propiedad,
                        'tipo_propiedad' => $record->pivot->tipo_propiedad,
                        'fecha_inicio' => $record->pivot->fecha_inicio,
                        'fecha_fin' => $record->pivot->fecha_fin, // Agregado por si acaso
                        'documento_sustento' => $record->pivot->documento_sustento,
                        'vigente' => (bool) $record->pivot->vigente, // <--- CRUCIAL
                    ])
                    // C. SAVE
                    ->action(function ($record, array $data): void {
                        $record->pivot->update($data);

                        Notification::make()
                            ->title('Condiciones actualizadas')
                            ->success()
                            ->send();
                    }),

                // 2. DETACH ACTION (Nativo)
                DetachAction::make()
                    ->label('Desvincular'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
