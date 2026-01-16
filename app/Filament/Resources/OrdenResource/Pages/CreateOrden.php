<?php

namespace App\Filament\Resources\OrdenResource\Pages;

use App\Filament\Resources\OrdenResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\FcmService;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrdenCreada;

class CreateOrden extends CreateRecord
{
    protected static string $resource = OrdenResource::class;

    protected function afterCreate(): void
    {
        $orden = $this->record;

        if ($orden->technician_id) {
            $tecnico = User::find($orden->technician_id);

            // Si el técnico no existe o no tiene tokens registrados, notificamos al operador y salimos.
            if ($tecnico && $tecnico->fcmTokens()->count() > 0) {
                // Si el técnico sí tiene tokens, preparamos y enviamos la notificación a todos sus dispositivos.
                // Ya no verificamos si fue exitoso o no, para evitar el mensaje de error confuso.
                // La propia FcmService ya registra los errores en el log para que tú los veas.
                $notification = [
                    'title' => '¡Nueva Orden Asignada!',
                    'body' => "Se te ha asignado la orden #{$orden->id}.",
                ];
                $data = ['order_id' => (string) $orden->id];

                app(FcmService::class)->sendToUser($tecnico, $notification, $data);
            } else {
                Notification::make()
                    ->title('Técnico sin Dispositivo Registrado')
                    ->body('La orden se creó, pero el técnico no puede recibir notificaciones push.')
                    ->warning()
                    ->persistent()
                    ->send();
            }
        }

        // --- Nueva Lógica de Email al Cliente ---
        if ($orden->cliente && $orden->cliente->email) {
            try {
                Mail::to($orden->cliente->email)->send(new OrdenCreada($orden));
            } catch (\Exception $e) {
                Notification::make()
                    ->title('Error al enviar correo')
                    ->body('La orden se creó, pero no se pudo enviar el correo al cliente: ' . $e->getMessage())
                    ->warning()
                    ->send();
            }
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Orden Creada')
            ->body('La orden ha sido creada exitosamente.');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
