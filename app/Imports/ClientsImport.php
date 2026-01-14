<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Filament\Notifications\Notification;


class ClientsImport implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue, WithEvents
{
    use Queueable;

    private $created = 0;
    private $updated = 0;
    private $skipped = 0;
    private $seenCedulas = [];
    private $seenCodigos = [];

    public function __construct(
        protected User $importedBy
    ) {
        ini_set('memory_limit', '1024M');
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                $key = 'import_progress_' . $this->importedBy->id;
                $stats = \Illuminate\Support\Facades\Cache::get($key);

                $created = $stats['created'] ?? 0;
                $updated = $stats['updated'] ?? 0;
                $skipped = $stats['skipped'] ?? 0;

                Notification::make()
                    ->title('Importación finalizada')
                    ->body("La carga de clientes ha terminado.\n\n✅ Creados: {$created}\n🔄 Actualizados: {$updated}\n⚠️ Omitidos: {$skipped}")
                    ->success()
                    ->sendToDatabase($this->importedBy);

                \Illuminate\Support\Facades\Cache::forget($key);
            },
            \Maatwebsite\Excel\Events\BeforeImport::class => function (\Maatwebsite\Excel\Events\BeforeImport $event) {
                // REMOVED: getReader()->getTotalRows() causes memory exhaustion on large files.
                // We set an estimation or just 0 to indicate "Unknown".
                
                \Illuminate\Support\Facades\Cache::put('import_progress_' . $this->importedBy->id, [
                    'total' => 0, // 0 indicates unknown/unlimited
                    'processed' => 0,
                    'created' => 0,
                    'updated' => 0,
                    'skipped' => 0,
                    'status' => 'running'
                ], 3600);
            }
        ];
    }

    // ... (previous code) ...

    public function collection(Collection $rows)
    {
        // 0. Check cancellation
        if (\Illuminate\Support\Facades\Cache::has('import_cancelled_' . $this->importedBy->id)) {
            \Illuminate\Support\Facades\Log::info("Skipping import chunk for user {$this->importedBy->id} due to cancellation.");
            \Illuminate\Support\Facades\Cache::forget('import_progress_' . $this->importedBy->id);
            return; // Abort processing this chunk
        }

        // Update progress in cache (increment processed count)
        $key = 'import_progress_' . $this->importedBy->id;
        $current = \Illuminate\Support\Facades\Cache::get($key);

        if ($current) {
            $current['processed'] += $rows->count();
            \Illuminate\Support\Facades\Cache::put($key, $current, 3600);
        }

        // 1. Gather all potential identifiers from this chunk to query DB once
        $cedulasToSearch = [];
        $codigosToSearch = [];
        $emailsToSearch = [];

        foreach ($rows as $row) {
            $cedula = $row['cedula'] ?? $row['cedula_de_ciudadania'] ?? null;
            $codigo = $row['codigo'] ?? $row['codigo_cliente'] ?? $row['codigo_de_contrato'] ?? null;
            $email = isset($row['correo']) ? strtolower(trim($row['correo'])) : null;

            if ($cedula) {
                $cedulasToSearch[] = $cedula;
                // Also check for the "allowed duplicate" version
                $cedulasToSearch[] = $cedula . '-2';
            }
            if ($codigo)
                $codigosToSearch[] = $codigo;
            if ($email)
                $emailsToSearch[] = $email;
        }

        // 2. Fetch existing users efficiently
        // We need full objects now to update them, not just availability checks.

        // Query 1: By Cedula
        $existingByCedula = new Collection();
        if (!empty($cedulasToSearch)) {
            $existingByCedula = User::whereIn('cedula', $cedulasToSearch)->get();
        }

        // Query 2: By Codigo
        $existingByCodigo = new Collection();
        if (!empty($codigosToSearch)) {
            $existingByCodigo = User::whereIn('codigo_contrato', $codigosToSearch)->get();
        }

        // Fetch existing emails with ID to check ownership
        $existingEmails = [];
        if (!empty($emailsToSearch)) {
            // Map: email (lower) => user_id
            $existingEmails = User::whereIn('email', $emailsToSearch)
                ->get()
                ->mapWithKeys(fn($u) => [strtolower($u->email) => $u->id])
                ->toArray();
        }

        // Map for faster lookup: 'cedula_XXX' => User, 'codigo_YYY' => User
        $existingMap = [];
        foreach ($existingByCedula as $u) {
            if ($u->cedula)
                $existingMap['cedula_' . $u->cedula] = $u;
        }
        foreach ($existingByCodigo as $u) {
            if ($u->codigo_contrato)
                $existingMap['codigo_' . $u->codigo_contrato] = $u;
        }

        // Track emails seen in this chunk to prevent collisions within the file
        $chunkSeenEmails = [];

        // Track local stats
        $chunkCreated = 0;
        $chunkUpdated = 0;
        $chunkSkipped = 0;

        // 3. Process rows
        foreach ($rows as $row) {
            // Strict check for critical columns
            if (
                !isset($row['cedula']) && !isset($row['codigo']) &&
                !isset($row['cedula_de_ciudadania']) && !isset($row['codigo_cliente']) && !isset($row['codigo_de_contrato'])
            ) {
                continue;
            }

            $cedula = $row['cedula'] ?? $row['cedula_de_ciudadania'] ?? null;
            $codigo = $row['codigo'] ?? $row['codigo_cliente'] ?? $row['codigo_de_contrato'] ?? null;

            if (empty($cedula) && empty($codigo)) {
                continue;
            }

            // --- UPSERT LOGIC ---

            // 1. Identify Existing User
            // 1. Identify Existing User
            $existingUser = null;
            // PRIORITY CHANGE: Check Cedula FIRST.
            // This prevents the case where we find a user by code, try to update them with a cedula that 
            // ALREADY belongs to another user (who wasn't found because we looked up code first),
            // triggering a unique constraint error.
            if ($cedula && isset($existingMap['cedula_' . $cedula])) {
                $existingUser = $existingMap['cedula_' . $cedula];
            } elseif ($codigo && isset($existingMap['codigo_' . $codigo])) {
                $existingUser = $existingMap['codigo_' . $codigo];
            }

            // 2. Prepare Data (with potential email adjustment, done later)
            // But strict unique checks depend on whether we are creating or updating.

            // Handle Email Uniqueness
            $email = isset($row['correo']) ? strtolower(trim($row['correo'])) : null;
            if (empty($email)) {
                $email = strtolower(($cedula ?? $codigo) . '@intalnet.com');
            }

            // Check collision
            // Collision if:
            // 1. Email exists in DB AND (We are creating OR We are updating a user who DOES NOT own this email)
            // 2. Email seen in this chunk (always collision unless we can somehow map that too, but assume duplicate row in chunk = collision for safety or we are processing same user twice)

            $isCollision = false;

            if (isset($chunkSeenEmails[$email])) {
                $isCollision = true;
            } elseif (isset($existingEmails[$email])) {
                $ownerId = $existingEmails[$email];
                if (!$existingUser) {
                    // Creating new user, but email taken -> Collision
                    $isCollision = true;
                } else {
                    // Updating user. Is it MY email?
                    if ($ownerId != $existingUser->id) {
                        // Taken by someone else -> Collision
                        $isCollision = true;
                    }
                }
            }

            if ($isCollision) {
                // Uniquify
                $parts = explode('@', $email);
                if (count($parts) == 2) {
                    $uniqueSuffix = $cedula ?? $codigo ?? rand(1000, 9999);
                    $email = $parts[0] . '.' . $uniqueSuffix . '@' . $parts[1];
                } else {
                    $email = strtolower(($cedula ?? $codigo) . '@intalnet.com');
                }
            }

            // Mark new email as seen
            $chunkSeenEmails[$email] = true;
            $row['correo'] = $email;

            if ($existingUser) {
                // UPDATE
                $mappedData = $this->mapRowToData($row, $cedula, $codigo, $email);
                // Exclude immutable fields or sensitive ones
                unset($mappedData['password']);
                unset($mappedData['created_at']);

                // We update EVERYTHING mapped.
                $existingUser->fill($mappedData);

                if ($existingUser->isDirty()) {
                    $existingUser->save();
                    $this->updated++;
                    $chunkUpdated++;
                } else {
                    $this->skipped++;
                    $chunkSkipped++;
                }

                // Update the map in case we found it by one key and now it has both, or changed one?
                // Actually safer not to mess with map mid-loop unless strict need.
            } else {
                // CHECK duplicate in FILE (Global memory) - Only if we didn't find in DB, check if we just created it in previous chunk?
                // seenCodigos/Cedulas track things created in this import session.

                // If we found it in DB ($existingUser), we updated it.
                // If not in DB, we check if we created it just now.

                $justCreated = false;
                if ($codigo && isset($this->seenCodigos[$codigo]))
                    $justCreated = true;
                if ($cedula && isset($this->seenCedulas[$cedula]))
                    $justCreated = true;

                if ($justCreated) {
                    // It means it was not in DB when we started, but we created it in a previous row/chunk.
                    // Ideally we should UPDATE that one too?
                    // But we don't have the object easily.
                    // For now, retain OLD behavior for "Duplicate in File but not DB" -> Skip/Log?
                    // Or, just skip to avoid unique error.
                    $this->skipped++;
                    $chunkSkipped++;
                    continue;
                }

                // CREATE
                $this->createUser($row, $cedula, $codigo, $email);
                $this->created++;
                $chunkCreated++;

                // Add to seen
                if ($cedula)
                    $this->seenCedulas[$cedula] = true;
                if ($codigo)
                    $this->seenCodigos[$codigo] = true;
            }
        }

        // Update Stats in Cache
        $current = \Illuminate\Support\Facades\Cache::get($key);
        if ($current) {
            $current['created'] = ($current['created'] ?? 0) + $chunkCreated;
            $current['updated'] = ($current['updated'] ?? 0) + $chunkUpdated;
            $current['skipped'] = ($current['skipped'] ?? 0) + $chunkSkipped;
            \Illuminate\Support\Facades\Cache::put($key, $current, 3600);
        }
    }

    private function mapRowToData($row, $cedula, $codigo, $email)
    {
        $data = [
            'name' => $row['nombres_y_apellidos'] ?? $row['nombres'] ?? 'Sin Nombre',
            'email' => $email,
            'cedula' => $cedula,
            'codigo_contrato' => $codigo,
            'direccion' => $row['direccion'] ?? null,
            'estrato' => $row['estrato'] ?? null,
            'zona' => $row['zona'] ?? null,
            'barrio' => $row['barrio'] ?? null,
            'telefono' => $row['telefono_facturacion'] ?? $row['telefono'] ?? null,
            'telefono_facturacion' => $row['telefono_facturacion'] ?? null,
            'otro_telefono' => $row['otro_telefono'] ?? null,

            'tipo_servicio' => $row['tipo_servicio'] ?? null,
            'vendedor' => $row['vendedor'] ?? null,
            'tipo_operacion' => $row['tipo_operacion'] ?? null,

            'suscripcion_tv' => $this->parseDate($row['suscripcion_tv'] ?? null),
            'suscripcion_internet' => $this->parseDate($row['suscripcion_internet'] ?? null),
            'fecha_ultimo_pago' => $this->parseDate($row['fecha_ultimo_pago'] ?? null),

            'estado_tv' => $this->parseStatus($row['estado_tv'] ?? null),
            'estado_internet' => $this->parseStatus($row['estado_internet'] ?? null),

            'saldo_tv' => $row['saldo_tv'] ?? 0,
            'saldo_internet' => $row['saldo_internet'] ?? 0,
            'saldo_otros' => $row['saldo_otros'] ?? 0,
            'saldo_total' => $row['saldo_total'] ?? 0,

            'tarifa_tv' => $row['tarifa_tv'] ?? 0,
            'tarifa_internet' => $row['tarifa_internet'] ?? 0,
            'tarifa_total' => $row['tarifa_total'] ?? 0,

            'plan_internet' => $row['plan_internet'] ?? null,
            'velocidad' => $row['velocidad'] ?? null,

            'cortado_tv' => $row['cortado_tv'] ?? null,
            'retiro_tv' => $row['retiro_tv'] ?? null,
            'cortado_int' => $row['cortado_int'] ?? null,
            'retiro_int' => $row['retiro_int'] ?? null,

            'serial' => $row['serial'] ?? null,
            'mac' => $row['mac'] ?? null,
            'ip' => $row['ip'] ?? null,
            'marca' => $row['marca'] ?? null,
        ];

        return $data;
    }

    private function createUser($row, $cedula, $codigo, $email)
    {
        $data = $this->mapRowToData($row, $cedula, $codigo, $email);

        // Clean and truncate password base (cedula) to avoid Bcrypt 'too long' error
        $passBase = $cedula ?? '123456';
        if (strlen($passBase) > 30) {
            $passBase = substr($passBase, 0, 30);
        }
        $data['password'] = $passBase;
        $data['created_at'] = now();

        // ... rest of create logic ...

        // Try to create user with retry logic for unique constraint violations
        $attempts = 0;
        $maxAttempts = 3;
        $user = null;

        while ($attempts < $maxAttempts) {
            try {
                $user = User::create($data);
                break; // Success
            } catch (QueryException $e) {
                // Check if it's a unique constraint violation (1062)
                if ($e->errorInfo[1] == 1062) {
                    $attempts++;
                    // Modify email if that was the issue (most likely)
                    if (str_contains($e->getMessage(), 'users_email_unique')) {
                        $parts = explode('@', $data['email']);
                        $data['email'] = $parts[0] . '.' . rand(10000, 99999) . '@' . ($parts[1] ?? 'intalnet.com');
                    }
                    // Modify cedula if that was the issue (less likely due to prior checks but possible)
                    elseif (str_contains($e->getMessage(), 'users_cedula_unique')) {
                        $data['cedula'] = $data['cedula'] . '-' . rand(100, 999);
                    } else {
                        throw $e; // Re-throw if it's another unique constraint or unknown
                    }
                } else {
                    throw $e; // Re-throw non-duplicate errors
                }
            }
        }

        if ($user) {
            $user->assignRole('cliente');
        } else {
            // Log failure after max attempts
            Log::error("Failed to create user after {$maxAttempts} attempts due to unique constraints: " . json_encode($data));
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function getCreatedCount()
    {
        return $this->created;
    }

    public function getSkippedCount()
    {
        return $this->skipped;
    }

    private function parseDate($value)
    {
        if (empty($value) || $value === 'NaT') {
            return null;
        }
        try {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
        } catch (\Throwable $e) {
            try {
                return Carbon::parse($value);
            } catch (\Throwable $e2) {
                return null;
            }
        }
    }

    private function parseStatus($value)
    {
        $value = trim($value ?? '');
        return empty($value) ? 'I' : $value;
    }
}
