<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use App\Models\User;
use App\Models\OrdenFoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;
use App\Http\Resources\OrdenFotoResource;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrdenEnProceso;

class OrderController extends Controller
{
    /**
     * Devuelve las órdenes asignadas al técnico autenticado.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Empezamos la consulta
        // Empezamos la consulta
        $query = Orden::with('cliente')->where('technician_id', $user->id);

        // Aplicamos el filtro de estado si viene en la petición
        if ($request->has('status') && $request->status !== 'todas') {
            $query->where('estado_orden', $request->status);
        }

        // Filtro por barrio
        if ($request->has('barrio') && !empty($request->barrio)) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('barrio', $request->barrio);
            });
        }

        // Buscador
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('numero_orden', 'like', "%{$searchTerm}%")
                    ->orWhere('nombre_cliente', 'like', "%{$searchTerm}%")
                    ->orWhere('direccion', 'like', "%{$searchTerm}%")
                    ->orWhere('cedula', 'like', "%{$searchTerm}%");
            });
        }

        // Ordenamos por la más reciente y paginamos los resultados
        $orders = $query->latest()->paginate(15); // Muestra 15 órdenes por página

        return response()->json($orders);
    }

    /**
     * Muestra los detalles de una orden específica.
     */
    public function show(Request $request, Orden $orden)
    {
        if ($request->user()->id !== $orden->technician_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $orden->load('cliente');

        return response()->json($orden);
    }

    /**
     * Permite al técnico "tomar" una orden, cambiando su estado.
     */
    /**
     * Permite al técnico "tomar" una orden, cambiando su estado.
     */
    public function acceptOrder(Request $request, Orden $orden)
    {
        $orden->refresh();
        $user = $request->user();

        if ($orden->technician_id !== $user->id) {
            return response()->json(['message' => 'No autorizado para modificar esta orden.'], 403);
        }

        $estado = trim(strtolower($orden->estado_orden ?? ''));

        // Idempotencia
        if ($estado === 'en_proceso') {
            return response()->json([
                'message' => 'Orden ya está en proceso.',
                'order' => $orden
            ]);
        }

        if ($estado !== 'asignada') {
            return response()->json([
                'message' => "La orden no se puede tomar. Estado actual: '$estado'. Se esperaba: 'asignada'."
            ], 422);
        }

        // Use Direct DB Update
        \Illuminate\Support\Facades\DB::table('ordens')
            ->where('id', $orden->id)
            ->update([
                'estado_orden' => 'en_proceso',
                'fecha_inicio_atencion' => now(),
                'updated_at' => now(),
            ]);

        $orden->refresh();

        // Enviar correo al cliente
        if ($orden->cliente && $orden->cliente->email) {
            try {
                Mail::to($orden->cliente->email)->send(new OrdenEnProceso($orden));
            } catch (\Exception $e) {
                // Loguear error pero no detener la ejecución
                \Illuminate\Support\Facades\Log::error('Error enviando correo OrdenEnProceso: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Orden tomada y en proceso.',
            'order' => $orden
        ]);
    }

    public function reportOnSite(Request $request, Orden $orden)
    {
        $orden->refresh();
        $user = $request->user();

        if ($orden->technician_id !== $user->id) {
            return response()->json(['message' => 'No autorizado para modificar esta orden.'], 403);
        }

        $estado = trim(strtolower($orden->estado_orden ?? ''));

        // Idempotencia
        if ($estado === 'en_sitio') {
            return response()->json([
                'message' => 'Orden ya reportada en sitio.',
                'order' => $orden
            ]);
        }

        if ($estado !== 'en_proceso') {
            return response()->json([
                'message' => "No se puede reportar en sitio. Estado actual: '$estado'. Se esperaba: 'en_proceso'."
            ], 422);
        }

        \Illuminate\Support\Facades\DB::table('ordens')
            ->where('id', $orden->id)
            ->update([
                'estado_orden' => 'en_sitio',
                'fecha_llegada' => now(),
                'updated_at' => now(),
            ]);

        $orden->refresh();

        return response()->json([
            'message' => 'Reporte en sitio exitoso.',
            'order' => $orden
        ]);
    }

    public function closeOrder(Request $request, Orden $orden)
    {
        $orden->refresh();
        $user = $request->user();

        if ($orden->technician_id !== $user->id) {
            return response()->json(['message' => 'No autorizado para modificar esta orden.'], 403);
        }

        $estado = trim(strtolower($orden->estado_orden ?? ''));

        // Idempotencia
        if ($estado === 'ejecutada') {
            return response()->json([
                'message' => 'Orden ya fue ejecutada.',
                'order' => $orden
            ]);
        }

        $solucion = $request->input('solucion_tecnico'); 

        // CRITICAL FIX: Normalize input. If it's a string, wrap in array.
        // This handles cases where client sends "Reprogramar" as string instead of ["Reprogramar"]
        if ($solucion && !is_array($solucion)) {
            // Check if it's a JSON string by chance, otherwise just wrap
            $decoded = json_decode($solucion, true);
            if (is_array($decoded)) {
                $solucion = $decoded;
            } else {
                $solucion = [$solucion];
            }
            // Merge back to request so validation sees it as array
            $request->merge(['solucion_tecnico' => $solucion]);
        }
        // If array, take the first one or logic is different?
        // Actually, $isSpecialCase checking needs to handle array now.
        // Assuming special cases (Reprogramar, Solicitar Cierre) act as single selections or flags
        
        $isSpecialCase = false;
        if (is_array($solucion)) {
            // Check if any of the special values are in the array
            $isSpecialCase = in_array('Solicitar Cierre', $solucion) || in_array('Reprogramar', $solucion);
            // If strictly one or the other is required logic, we might need to adjust, but broadly checking if present is safer.
        } else {
             $isSpecialCase = in_array($solucion, ['Solicitar Cierre', 'Reprogramar']);
        }

        if (!$isSpecialCase && $estado !== 'en_sitio') {
            return response()->json([
                'message' => "No se puede finalizar la orden. Estado actual: '$estado'. Se esperaba: 'en_sitio'."
            ], 422);
        }

        // $solucion already retrieved above
        // $isSpecialCase already defined above

        $validated = $request->validate([
            'celular' => 'nullable|string|max:20',
            'observaciones' => $isSpecialCase ? 'required|string' : 'nullable|string',
            'solucion_tecnico' => 'nullable|array', // CHANGED TO ARRAY
            'solucion_tecnico.*' => 'string', // Validate contents
            'firma_tecnico' => $isSpecialCase ? 'nullable' : 'required',
            'firma_suscriptor' => $isSpecialCase ? 'nullable' : 'required',
            'articulos' => 'nullable|array',
            'mac_router' => 'nullable|string',
            'mac_bridge' => 'nullable|string',
            'mac_ont' => 'nullable|string',
            'otros_equipos' => 'nullable|string',
        ]);

        $nuevoEstado = 'ejecutada'; // Default
        $technicianId = $orden->technician_id;

        if ($isSpecialCase) {
            if (in_array('Reprogramar', $solucion)) {
                $nuevoEstado = 'pendiente';
                $technicianId = null;
            } elseif (in_array('Solicitar Cierre', $solucion)) {
                $nuevoEstado = 'pendiente';
                $technicianId = null;
            }
        }

        $updateData = [
            'estado_orden' => $nuevoEstado,
            'technician_id' => $technicianId,
            'fecha_fin_atencion' => now(),
            // Keep other fields
            'telefono' => $validated['celular'] ?? $orden->telefono,
            'observaciones' => $validated['observaciones'] ?? $orden->observaciones,
            'solucion_tecnico' => isset($validated['solucion_tecnico']) ? json_encode($validated['solucion_tecnico']) : (is_array($orden->solucion_tecnico) ? json_encode($orden->solucion_tecnico) : $orden->solucion_tecnico),
            'firma_tecnico' => $validated['firma_tecnico'],
            'firma_suscriptor' => $validated['firma_suscriptor'],
            'articulos' => isset($validated['articulos']) ? json_encode($validated['articulos']) : $orden->articulos,
            'mac_router' => $validated['mac_router'] ?? $orden->mac_router,
            'mac_bridge' => $validated['mac_bridge'] ?? $orden->mac_bridge,
            'mac_ont' => $validated['mac_ont'] ?? $orden->mac_ont,
            'otros_equipos' => $validated['otros_equipos'] ?? $orden->otros_equipos,
            'updated_at' => now(),
        ];

        // Reset timer if Reprogrammed
        if ($isSpecialCase && in_array('Reprogramar', $solucion)) {
            $updateData['created_at'] = now();
        }

        \Illuminate\Support\Facades\DB::table('ordens')
            ->where('id', $orden->id)
            ->update($updateData);

        // Logic for Notification if Reprogrammed or Closure Requested
        if ($isSpecialCase) {
             // Determine which one for notification details
             $solucionType = in_array('Reprogramar', $solucion) ? 'Reprogramar' : 'Solicitar Cierre';
             
             // We need to re-set these variables inside the block because they were based on string comparison before
             $title = $solucionType === 'Reprogramar' ? 'Orden Reprogramada' : 'Solicitud de Cierre';
             $icon = $solucionType === 'Reprogramar' ? 'heroicon-o-arrow-path-rounded-square' : 'heroicon-o-check-circle';
             $actionText = $solucionType === 'Reprogramar' ? 'reprogramó' : 'solicitó cierre para';
             
             $recipients = User::role(['administrador', 'operador'])->get();

             $notification = FilamentNotification::make()
                ->title($title)
                ->icon($icon)
                ->body("El técnico {$user->name} {$actionText} la orden #{$orden->numero_orden}. Motivo: \"{$validated['observaciones']}\".") 
                ->actions([
                    Action::make('view')
                        ->label('Ver Orden')
                        ->url(route('filament.admin.resources.ordens.edit', ['record' => $orden])),
                ]);

            foreach ($recipients as $recipient) {
                $notification->sendToDatabase($recipient);
            }
        }

        $orden->refresh();

        return response()->json([
            'message' => 'Orden finalizada exitosamente.',
            'order' => $orden
        ]);
    }

    /**
     * Permite al técnico rechazar una orden y notifica directamente a los administradores/operadores.
     */
    public function rejectOrder(Request $request, Orden $orden)
    {
        $user = $request->user();

        if ($orden->technician_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if (strtolower($orden->estado_orden) !== 'asignada') {
            return response()->json(['message' => 'Esta orden ya no se puede rechazar.'], 422);
        }

        // Actualiza la orden
        $orden->estado_orden = 'rechazada'; // Or use DB facade if preferred, but save() is fine here if no conflict
        $orden->technician_id = null;
        $orden->save();

        // --- Lógica de Notificación Directa ---

        $recipients = User::role(['administrador', 'operador'])->get();

        $notification = FilamentNotification::make()
            ->title('Orden Rechazada')
            ->icon('heroicon-o-exclamation-triangle')
            ->body("El técnico {$user->name} ha rechazado la orden #{$orden->numero_orden}. Se requiere reasignación.")
            ->actions([
                Action::make('view')
                    ->label('Ver Orden')
                    ->url(route('filament.admin.resources.ordens.edit', ['record' => $orden])),
            ])
            ->danger();

        foreach ($recipients as $recipient) {
            $notification->sendToDatabase($recipient);
        }

        return response()->json(['message' => 'Orden rechazada correctamente.']);
    }
    public function updateDetails(Request $request, Orden $orden)
    {
        // Validación de los datos de entrada
        $validatedData = $request->validate([
            'celular' => 'nullable|string|max:20',
            'observaciones' => 'nullable|string',
        ]);

        $orden->update($validatedData);

        return response()->json([
            'message' => 'Detalles de la orden actualizados.',
            'order' => $orden,
        ]);
    }

    /**
     * Sube una foto para una orden específica.
     */
    public function uploadPhoto(Request $request, Orden $orden)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Guarda la foto en el disco 'public' para que sea accesible
        $path = $request->file('photo')->store('orden-fotos', 'public');

        // Crea el registro en la base de datos
        $orden->fotos()->create(['path' => $path]);

        return response()->json(['message' => 'Foto subida exitosamente.']);
    }

    public function getPhotos(Request $request, Orden $orden)
    {
        // Opcional: Verificación de autorización
        if ($request->user()->id !== $orden->technician_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $fotos = $orden->fotos->map(function ($foto) {
            return [
                'id' => $foto->id,
                'orden_id' => $foto->orden_id,
                'path' => $foto->path,
                'url' => url('/api/v1/private-fotos/' . $foto->id), // Se construye la URL correcta
                'created_at' => $foto->created_at,
                'updated_at' => $foto->updated_at,
            ];
        });

        return response()->json($fotos);
    }
    public function showPhoto(Request $request, OrdenFoto $ordenFoto)
    {
        // Opcional: Verificación de autorización para asegurar que solo el técnico
        // asignado a la orden de esta foto pueda verla.
        if ($request->user()->id !== $ordenFoto->orden->technician_id) {
            abort(403, 'No autorizado.');
        }

        // Primero buscar en disco público (nuevas fotos)
        if (Storage::disk('public')->exists($ordenFoto->path)) {
            return Storage::disk('public')->response($ordenFoto->path);
        }

        // Si no, buscar en disco local (fotos antiguas)
        if (Storage::disk('local')->exists($ordenFoto->path)) {
            return Storage::disk('local')->response($ordenFoto->path);
        }

        abort(404, 'Imagen no encontrada.');
    }

    /**
     * Devuelve las órdenes pendientes sin asignar (para que el técnico solicite).
     */
    public function getPendingOrders(Request $request)
    {
        $query = Orden::with('cliente')
            ->where('estado_orden', 'pendiente')
            ->whereNull('technician_id');

        // Filtros (Clasificación, Tipo)
        if ($request->has('clasificacion') && $request->clasificacion) {
            $query->where('clasificacion', $request->clasificacion);
        }
        if ($request->has('tipo_orden') && $request->tipo_orden) {
            $query->where('tipo_orden', $request->tipo_orden);
        }

        if ($request->has('barrio') && !empty($request->barrio)) {
            $query->whereHas('cliente', function ($q) use ($request) {
                $q->where('barrio', $request->barrio);
            });
        }

        $orders = $query->latest('created_at')->paginate(20);

        return response()->json($orders);
    }

    /**
     * Permite al técnico solicitar (auto-asignarse) una orden pendiente.
     */
    public function claimOrder(Request $request, Orden $orden)
    {
        $user = $request->user();

        if ($orden->technician_id !== null) {
            return response()->json(['message' => 'Esta orden ya fue asignada a otro técnico.'], 422);
        }

        if ($orden->estado_orden !== 'pendiente') {
            return response()->json(['message' => 'La orden no está en estado pendiente.'], 422);
        }

        $orden->update([
            'technician_id' => $user->id,
            'estado_orden' => 'asignada',
            'fecha_asignacion' => now(),
        ]);

        // Notificar Admins
        $recipients = User::role(['administrador', 'operador'])->get();
        $notification = FilamentNotification::make()
            ->title('Orden Asignada')
            ->body("El técnico {$user->name} ha tomado la orden #{$orden->numero_orden} desde la App.")
            ->info();

        foreach ($recipients as $recipient) {
            $notification->sendToDatabase($recipient);
        }

        return response()->json([
            'message' => 'Orden asignada correctamente.',
            'order' => $orden
        ]);
    }

    /**
     * Devuelve la lista de barrios (únicos) de los clientes existentes.
     */
    public function getBarrios(Request $request)
    {
        // Obtener barrios distintos de usuarios con rol 'cliente'
        // Asumiendo que User::role('cliente') funciona (requiere Spatie Permission o lógica custom)
        // O simplemente buscamos en la tabla users donde tenga barrio no nulo.

        $barrios = User::whereNotNull('barrio')
            ->where('barrio', '!=', '')
            ->distinct()
            ->pluck('barrio')
            ->sort()
            ->values();

        return response()->json($barrios);
    }
}