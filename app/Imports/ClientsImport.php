<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use Illuminate\Support\Facades\Log;

class ClientsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
        // Validate Headers are present (basic check based on first row of data)
        // Note: WithHeadingRow handles matching, but if keys are missing from the row array, it means headers were not found.
        
        // Strict check for critical columns
        if (!array_key_exists('cedula', $row) && !array_key_exists('codigo', $row)) {
             // If neither of our expected keys exist, it's likely the headers are wrong.
             // We can throw an exception to stop import and notify user.
             // However, to be user-friendly, we might want to fail fast.
             
             // Check if this looks like a header mismatch globally (only checking first row effectively but model calls for each)
             // We'll throw an exception that Filament can catch or users see.
             throw new \Exception("Error en el formato del archivo: No se encontraron las columnas 'Cedula' o 'Codigo' en la primera fila. Verifique los títulos.");
        }

        // Normalize keys if needed or check multiple variations
        $cedula = $row['cedula'] ?? $row['cedula_de_ciudadania'] ?? null;
        $codigo = $row['codigo'] ?? $row['codigo_cliente'] ?? $row['codigo_de_contrato'] ?? null;

        // Check if required fields exist
        if (empty($cedula) && empty($codigo)) {
            Log::warning('Skipping row due to missing identifiers: ' . json_encode($row));
            return null;
        }

        // Try to find existing user by Cedula or Codigo
        $user = null;
        if ($cedula) {
            $user = User::where('cedula', $cedula)->first();
        }
        if (!$user && $codigo) {
            $user = User::where('codigo_contrato', $codigo)->first();
        }

        $data = [
            'name' => $row['nombres_y_apellidos'] ?? $row['nombres'] ?? 'Sin Nombre',
            'email' => $row['correo'] ?? null,
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

        // Ensure email is not null if creating
        if (!$user && empty($data['email'])) {
            $data['email'] = ($cedula ?? $codigo) . '@intalnet.com';
        }

        if ($user) {
            if (empty($data['email'])) {
                unset($data['email']);
            }
            $user->update($data);
            return $user;
        } else {
            $data['password'] = Hash::make($cedula ?? '123456');
            $data['created_at'] = now();
            $user = User::create($data);
            $user->assignRole('cliente');
            return $user;
        }
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
