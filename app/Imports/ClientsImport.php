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

class ClientsImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private $created = 0;
    private $skipped = 0;
    
    // We don't strictly need persistent seen arrays for uniqueness check across chunks 
    // IF we trust the DB check. However, within a single file upload session, 
    // tracking seen items in memory helps avoid duplicates INSIDE the file.
    // For 50k rows, arrays are manageable.
    private $seenCedulas = [];
    private $seenCodigos = [];

    public function collection(Collection $rows)
    {
        // 1. Gather all potential identifiers from this chunk to query DB once
        $cedulasToSearch = [];
        $codigosToSearch = [];
        $emailsToSearch = [];

        foreach ($rows as $row) {
            $cedula = $row['cedula'] ?? $row['cedula_de_ciudadania'] ?? null;
            $codigo = $row['codigo'] ?? $row['codigo_cliente'] ?? $row['codigo_de_contrato'] ?? null;
            $email = $row['correo'] ?? null;

            if ($cedula) $cedulasToSearch[] = $cedula;
            if ($codigo) $codigosToSearch[] = $codigo;
            if ($email) $emailsToSearch[] = $email;
        }

        // 2. Fetch existing users in one query (for skipping)
        $existingUsers = User::query()
            ->where(function($q) use ($cedulasToSearch, $codigosToSearch) {
                if (!empty($cedulasToSearch)) $q->whereIn('cedula', $cedulasToSearch);
                if (!empty($codigosToSearch)) $q->orWhereIn('codigo_contrato', $codigosToSearch);
            })
            ->get();
            
        // Fetch existing emails to prevent duplicate email error
        $existingEmails = [];
        if (!empty($emailsToSearch)) {
            $existingEmails = User::whereIn('email', $emailsToSearch)->pluck('email', 'email')->toArray();
        }
        
        // Map for faster lookup: 'cedula_XXX' => true, 'codigo_YYY' => true
        $existingMap = [];
        foreach ($existingUsers as $u) {
            if ($u->cedula) $existingMap['cedula_' . $u->cedula] = true;
            if ($u->codigo_contrato) $existingMap['codigo_' . $u->codigo_contrato] = true;
        }
        
        // Track emails seen in this chunk to prevent collisions within the file
        $chunkSeenEmails = [];

        // 3. Process rows
        foreach ($rows as $row) {
             // Strict check for critical columns (only needed once effectively, but good for validation)
            if (!isset($row['cedula']) && !isset($row['codigo']) && 
                !isset($row['cedula_de_ciudadania']) && !isset($row['codigo_cliente']) && !isset($row['codigo_de_contrato'])) {
                 continue; 
            }

            $cedula = $row['cedula'] ?? $row['cedula_de_ciudadania'] ?? null;
            $codigo = $row['codigo'] ?? $row['codigo_cliente'] ?? $row['codigo_de_contrato'] ?? null;

            if (empty($cedula) && empty($codigo)) {
                continue;
            }

            // Check duplicate in FILE (Global memory)
            if (($cedula && isset($this->seenCedulas[$cedula])) || ($codigo && isset($this->seenCodigos[$codigo]))) {
                $this->skipped++;
                continue;
            }
            
            // Check duplicate in DB (Batch lookup)
            $existsInDb = ($cedula && isset($existingMap['cedula_' . $cedula])) || 
                          ($codigo && isset($existingMap['codigo_' . $codigo]));

            if ($existsInDb) {
                $this->skipped++;
                // Mark as seen so we don't process it again if repeated in file
                if ($cedula) $this->seenCedulas[$cedula] = true; 
                if ($codigo) $this->seenCodigos[$codigo] = true;
                continue;
            }

            // Mark as seen
            if ($cedula) $this->seenCedulas[$cedula] = true;
            if ($codigo) $this->seenCodigos[$codigo] = true;
            
            // Handle Email Uniqueness
            $email = $row['correo'] ?? null;
            if (empty($email)) {
                 $email = ($cedula ?? $codigo) . '@intalnet.com';
            }
            
            // Check collision with DB or current chunk
            if (isset($existingEmails[$email]) || isset($chunkSeenEmails[$email])) {
                // Email collision! Append cedula to make unique (e.g. user@gmail.com -> user.12345@gmail.com)
                // This preserves the domain but makes the local part unique.
                $parts = explode('@', $email);
                if (count($parts) == 2) {
                    $uniqueSuffix = $cedula ?? $codigo ?? rand(1000,9999);
                    $email = $parts[0] . '.' . $uniqueSuffix . '@' . $parts[1];
                } else {
                    // Fallback if format weird
                     $email = ($cedula ?? $codigo) . '@intalnet.com';
                }
            }
            
            // Mark new email as seen
            $chunkSeenEmails[$email] = true;
            
            // Override email in row data essentially
            $row['correo'] = $email;

            // CREATE USER
            $this->createUser($row, $cedula, $codigo, $email);
            $this->created++;
        }
    }

    private function createUser($row, $cedula, $codigo, $email)
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

        // Email is passed as argument, already ensured not null and unique


        $data['password'] = Hash::make($cedula ?? '123456');
        $data['created_at'] = now();
        
        $user = User::create($data);
        $user->assignRole('cliente');
    }

    public function chunkSize(): int
    {
        return 1000;
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
