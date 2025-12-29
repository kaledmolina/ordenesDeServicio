<?php
   
   namespace App\Filament\Resources\OrdenResource\Pages;
   
   use App\Filament\Resources\OrdenResource;
   use Filament\Resources\Pages\CreateRecord;
   use App\Services\FcmService;
   use App\Models\User;
   use Filament\Notifications\Notification;
   
   class CreateOrden extends CreateRecord
   {
       protected static string $resource = OrdenResource::class;
   
       protected function afterCreate(): void
       {
           $orden = $this->record;
   
           // Si no se asignó un técnico, no hacemos nada más.
           if (!$orden->technician_id) {
               return;
           }
   
           $tecnico = User::find($orden->technician_id);
   
           // Si el técnico no existe o no tiene tokens registrados, notificamos al operador y salimos.
           if (!$tecnico || $tecnico->fcmTokens()->count() === 0) {
               Notification::make()
                   ->title('Técnico sin Dispositivo Registrado')
                   ->body('La orden se creó, pero el técnico no puede recibir notificaciones push.')
                   ->warning()
                   ->persistent()
                   ->send();
               return;
           }
   
           // Si el técnico sí tiene tokens, preparamos y enviamos la notificación a todos sus dispositivos.
           // Ya no verificamos si fue exitoso o no, para evitar el mensaje de error confuso.
           // La propia FcmService ya registra los errores en el log para que tú los veas.
           $notification = [
               'title' => '¡Nueva Orden Asignada!',
               'body' => "Se te ha asignado la orden #{$orden->id}.",
           ];
           $data = ['order_id' => (string)$orden->id];
   
           app(FcmService::class)->sendToUser($tecnico, $notification, $data);
       }
   
       protected function getCreatedNotification(): ?Notification
       {
           return Notification::make()
               ->success()
               ->title('Orden Creada')
               ->body('La orden ha sido creada exitosamente.');
       }
   }
