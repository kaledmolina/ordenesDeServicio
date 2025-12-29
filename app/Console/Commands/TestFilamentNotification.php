<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Orden;
use Illuminate\Console\Command;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;
use Illuminate\Support\Facades\Config; // <-- Importante: Añadir para leer la configuración

class TestFilamentNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // El comando se llamará: app:test-filament-notification
    protected $signature = 'app:test-filament-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba el envío de una notificación de Filament a los administradores y operadores.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Iniciando prueba de notificación de Filament...");

        // --- NUEVA VERIFICACIÓN DE LA COLA ---
        $queueDriver = Config::get('queue.default');
        $this->info("Verificando el driver de la cola: [{$queueDriver}]");

        if ($queueDriver !== 'sync') {
            $this->warn("¡Atención! Tu driver de cola no es 'sync'.");
            $this->warn("Las notificaciones se están añadiendo a la cola pero no se procesarán inmediatamente.");
            $this->warn("Para procesarlas, ejecuta 'php artisan queue:work' en otra terminal.");
            $this->warn("Para que se envíen al instante, cambia QUEUE_CONNECTION=sync en tu archivo .env");
        }
        // --- FIN DE LA VERIFICACIÓN ---

        // 1. Simular una orden y un técnico para el contexto de la notificación.
        $this->info("1. Creando datos de prueba (orden y técnico simulados)...");
        $orden = new Orden(['id' => 999, 'numero_orden' => 'TEST-001']);
        $tecnico = new User(['name' => 'Técnico de Prueba']);

        // 2. Obtener los destinatarios.
        $this->info("2. Buscando destinatarios con rol 'administrador' u 'operador'...");
        $recipients = User::role(['administrador', 'operador'])->get();

        if ($recipients->isEmpty()) {
            $this->error("No se encontraron destinatarios. Asegúrate de que haya usuarios con el rol 'administrador' u 'operador'.");
            return 1;
        }

        $this->info("Destinatarios encontrados: " . $recipients->count());
        foreach ($recipients as $recipient) {
            $this->line("- Usuario ID: {$recipient->id}, Email: {$recipient->email}");
        }

        // 3. Crear la notificación.
        $this->info("3. Creando el objeto de la notificación...");
        $notification = FilamentNotification::make()
            ->title('Orden Rechazada (PRUEBA)')
            ->icon('heroicon-o-exclamation-triangle')
            ->body("El técnico {$tecnico->name} ha rechazado la orden #{$orden->numero_orden}. Se requiere reasignación.")
            ->actions([
                Action::make('view')
                    ->label('Ver Orden')
                    // CORRECCIÓN: Pasamos el ID directamente porque el objeto $orden no es un modelo real de la BD.
                    ->url(route('filament.admin.resources.ordens.edit', ['record' => 999])),
            ])
            ->danger();

        // 4. Enviar la notificación a cada destinatario.
        $this->info("4. Enviando notificación a la base de datos para cada destinatario...");
        foreach ($recipients as $recipient) {
            $notification->sendToDatabase($recipient);
            $this->line("-> Notificación enviada a la base de datos para el usuario #{$recipient->id}.");
        }

        $this->info("✅ ¡Prueba finalizada! Revisa el panel de Filament de los usuarios administradores/operadores.");
        return 0;
    }
}