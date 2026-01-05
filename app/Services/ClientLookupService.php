<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ClientLookupService
{
    protected string $filePath;

    public function __construct()
    {
        // Path based on user request: app/json/clientes.json
        $this->filePath = app_path('json/clientes.json');
    }

    /**
     * Search for a client by Cedula, Codigo, or Name.
     * Returns the first matching record or null.
     */
    public function search(string $term): ?array
    {
        if (!File::exists($this->filePath)) {
            return null; // File not found
        }

        $jsonContent = File::get($this->filePath);
        $clients = json_decode($jsonContent, true);

        if (!is_array($clients)) {
            return null; // Invalid JSON
        }

        $term = strtolower(trim($term));

        foreach ($clients as $client) {
            // Check Codigo
            if (isset($client['Codigo']) && (string)$client['Codigo'] === $term) {
                return $this->normalizeClientData($client);
            }

            // Check Cedula
            if (isset($client['Cedula']) && (string)$client['Cedula'] === $term) {
                return $this->normalizeClientData($client);
            }

            // Check Name (Partial match?) - User asked for "Search (cedula or name or code)"
            // Strict equality for name might be clearer, or `str_contains` for flexibility.
            // Let's use loose comparison for flexibility but prioritize exact matches if needed.
            // For now, strict string comparison after lowercase.
            if (isset($client['Nombres y apellidos']) && str_contains(strtolower($client['Nombres y apellidos']), $term)) {
                return $this->normalizeClientData($client);
            }
        }

        return null;
    }

    /**
     * Map JSON keys to DB columns and format values.
     */
    protected function normalizeClientData(array $client): array
    {
        return [
            'name' => $client['Nombres y apellidos'] ?? null,
            'cedula' => $client['Cedula'] ?? null,
            'email' => $client['Correo'] ?? null,
            'direccion' => $client['Direccion'] ?? null,
            'codigo_contrato' => $client['Codigo'] ?? null,
            'estrato' => $client['Estrato'] ?? null,
            'zona' => $client['Zona'] ?? null,
            'barrio' => $client['Barrio'] ?? null,
            
            'telefono' => $client['Telefono facturacion'] ?? null,
            'telefono_facturacion' => $client['Telefono facturacion'] ?? null,
            'otro_telefono' => $client['Otro telefono'] ?? null,
            
            'tipo_servicio' => $client['tipo servicio'] ?? null,
            'vendedor' => $client['Vendedor'] ?? null,
            'tipo_operacion' => $client['Tipo operacion'] ?? null,
            
            'suscripcion_tv' => $this->formatDate($client['Suscripcion tv'] ?? null),
            'suscripcion_internet' => $this->formatDate($client['Suscripcion internet'] ?? null),
            'fecha_ultimo_pago' => $this->formatDate($client['Fecha ultimo pago'] ?? null),
            
            'estado_tv' => trim($client['Estado tv'] ?? '') ?: 'I', // Default to I if empty/space? User example has "A" or " "
            'estado_internet' => trim($client['Estado Internet'] ?? '') ?: 'I',
            
            'saldo_tv' => $client['Saldo tv'] ?? 0,
            'saldo_internet' => $client['Saldo internet'] ?? 0,
            'saldo_otros' => $client['Saldo otros'] ?? 0,
            'saldo_total' => $client['Saldo total'] ?? 0,
            
            'tarifa_tv' => $client['Tarifa tv'] ?? 0,
            'tarifa_internet' => $client['Tarifa Internet'] ?? 0,
            'tarifa_total' => $client['Tarifa total'] ?? 0,
            
            'plan_internet' => $client['Plan internet'] ?? null,
            'velocidad' => $client['Velocidad'] ?? null,
            
            'cortado_tv' => $client['Cortado tv'] ?? null,
            'retiro_tv' => $client['Retiro tv'] ?? null,
            'cortado_int' => $client['Cortado int'] ?? null,
            'retiro_int' => $client['Retiro int'] ?? null,
            
            'serial' => $client['Serial'] ?? null,
            'mac' => $client['Mac'] ?? null,
            'ip' => $client['Ip'] ?? null,
            'marca' => $client['Marca'] ?? null,
        ];
    }
    
    protected function formatDate($value)
    {
        if (empty($value) || $value === 'NaT') {
            return null;
        }
        // User format: "2023-06-26 00:00:00" -> keep only date part YYYY-MM-DD
        try {
            return Str::before($value, ' ');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
