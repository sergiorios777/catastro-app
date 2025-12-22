<?php

namespace App\Filament\App\Resources\Cajas\Pages;

use App\Filament\App\Resources\Cajas\CajaResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Models\Caja;

class CreateCaja extends CreateRecord
{
    protected static string $resource = CajaResource::class;

    // Antes de llenar el formulario, verificamos
    public function mount(): void
    {
        $cajaAbierta = Caja::where('user_id', auth()->id())
            ->where('estado', 'abierta')
            ->exists();

        if ($cajaAbierta) {
            Notification::make()
                ->title('Ya tienes una caja abierta')
                ->body('Debes cerrar tu caja actual antes de abrir una nueva.')
                ->danger()
                ->send();

            // Redirigir al listado
            $this->redirect($this->getResource()::getUrl('index'));
            return; // Importante detener
        }

        parent::mount();
    }

    // Al guardar, inyectamos los datos automáticos
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['fecha_apertura'] = now();
        $data['estado'] = 'abierta';
        $data['tenant_id'] = auth()->user()->tenant_id; // Si no lo hace el scope global

        return $data;
    }
}
