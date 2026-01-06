<?php

namespace App\Services;

use App\Models\FcmToken;
use App\Models\User;
use Illuminate\Support\Str;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Throwable;

class FcmService
{
    public function __construct() {}

    public function registerToken(User $user, string $token, ?string $deviceName = null): FcmToken
    {
        return FcmToken::updateOrCreate(
            [
                'user_id' => $user->getKey(),
                'token' => $token,
            ],
            [
                'device_name' => $deviceName,
            ]
        );
    }

    /**
     * @throws FirebaseException
     */
    public function sendToUser(User $user, array $notification, array $data = []): void
    {
        try {
            $tokens = $user->fcmTokens()->pluck('token')->filter()->values();

            if ($tokens->isEmpty()) {
                return;
            }

            $messaging = app(Messaging::class);

            foreach ($tokens as $token) {
                $payload = [
                    'token' => $token,
                    'notification' => $notification,
                ];

                if (! empty($data)) {
                    $normalizedData = [];

                    foreach ($data as $key => $value) {
                        $normalizedData[(string) $key] = (string) $value;
                    }

                    $payload['data'] = $normalizedData;
                }

                try {
                    $messaging->send($payload);
                } catch (MessagingException $exception) {
                    if ($this->shouldDeleteToken($exception)) {
                        FcmToken::where('token', $token)->delete();
                        continue;
                    }

                    throw $exception;
                }
            }
        } catch (Throwable $e) {
             // Capturamos cualquier error de Firebase (ej: credenciales faltantes) para no romper el flujo
             \Illuminate\Support\Facades\Log::warning('Fallo al enviar notificación Firebase: ' . $e->getMessage());

             // Si estamos en un entorno Filament (Admin Panel), mostramos una notificación visual
             if (class_exists(\Filament\Notifications\Notification::class)) {
                 \Filament\Notifications\Notification::make()
                     ->title('Advertencia: Notificación Push no enviada')
                     ->body('El sistema no pudo conectar con Firebase. Verifique las credenciales si esto es producción. Error: ' . $e->getMessage())
                     ->warning()
                     ->send();
             }
        }
    }

    private function shouldDeleteToken(Throwable $exception): bool
    {
        $message = Str::lower($exception->getMessage());

        return Str::contains($message, [
            'requested entity was not found',
            'notregistered',
            'invalid registration token',
            'registration-token-not-registered',
        ]);
    }
}
