<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Exception\FirebaseException;

class TestFirebaseConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase:test-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba la conexión a Firebase verificando las credenciales y listando usuarios.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando prueba de conexión a Firebase...');

        try {
            // Intentar obtener la instancia de Auth
            $auth = app('firebase.auth');
            
            $this->info("✅ Instancia de Auth cargada correctamente.");

            // Intentar una operación real (listar usuarios)
            $this->line('Intentando conectar con el servicio de Auth...');
            $users = $auth->listUsers(1);
            
            $this->info('✅ Conexión establecida exitosamente con Firebase Auth.');
            
            // Verificar Storage (opcional pero útil)
            try {
                $storage = app('firebase.storage');
                $bucket = $storage->getBucket();
                $this->info("✅ Conexión establecida exitosamente con Firebase Storage. Bucket: {$bucket->name()}");
            } catch (\Throwable $e) {
                $this->warn('⚠️  No se pudo verificar Firebase Storage (esto puede ser normal si no lo usas o no está configurado): ' . $e->getMessage());
            }

        } catch (FirebaseException $e) {
            $this->error('❌ Error de Firebase: ' . $e->getMessage());
            return Command::FAILURE;
        } catch (\Throwable $e) {
            $this->error('❌ Error general: ' . $e->getMessage());
            $this->line('Verifica que el archivo de credenciales exista y sea legible.');
            $this->line('Ruta configurada: ' . config('firebase.projects.app.credentials.file'));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
