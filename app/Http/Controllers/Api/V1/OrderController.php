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

        if ($estado !== 'en_sitio') {
            return response()->json([
                'message' => "No se puede finalizar la orden. Estado actual: '$estado'. Se esperaba: 'en_sitio'."
            ], 422);
        }

        $solucion = $request->input('solucion_tecnico');
        $isSpecialCase = in_array($solucion, ['Solicitar Cierre', 'Reprogramar']);

        $validated = $request->validate([
            'celular' => 'nullable|string|max:20',
            'observaciones' => $isSpecialCase ? 'required|string' : 'nullable|string',
            'solucion_tecnico' => 'nullable|string',
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

        if ($solucion === 'Reprogramar') {
            $nuevoEstado = 'pendiente';
            $technicianId = null;
        }

        \Illuminate\Support\Facades\DB::table('ordens')
            ->where('id', $orden->id)
            ->update([
                'estado_orden' => $nuevoEstado,
                'technician_id' => $technicianId,
                'fecha_fin_atencion' => now(),
                // Keep other fields
                'telefono' => $validated['celular'] ?? $orden->telefono,
                'observaciones' => $validated['observaciones'] ?? $orden->observaciones,
                'solucion_tecnico' => $validated['solucion_tecnico'] ?? $orden->solucion_tecnico,
                'firma_tecnico' => $validated['firma_tecnico'],
                'firma_suscriptor' => $validated['firma_suscriptor'],
                'articulos' => isset($validated['articulos']) ? json_encode($validated['articulos']) : $orden->articulos,
                'mac_router' => $validated['mac_router'] ?? $orden->mac_router,
                'mac_bridge' => $validated['mac_bridge'] ?? $orden->mac_bridge,
                'mac_ont' => $validated['mac_ont'] ?? $orden->mac_ont,
                'otros_equipos' => $validated['otros_equipos'] ?? $orden->otros_equipos,
                'updated_at' => now(),
            ]);

        // Logic for Notification if Reprogrammed
        if ($solucion === 'Reprogramar') {
            $recipients = User::role(['administrador', 'operador'])->get();
            $motivo = $validated['observaciones'] ?? 'Sin motivo especificado';

            $notification = FilamentNotification::make()
                ->title('Orden Reprogramada')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->body("El técnico {$user->name} reprogramó la orden #{$orden->numero_orden}. Motivo: \"{$motivo}\". Se requiere reasignación.")
                ->actions([
                    Action::make('view')
                        ->label('Ver Orden')
                        ->url(route('filament.admin.resources.ordens.edit', ['record' => $orden])),
                ])
                ->warning(); // Or danger/info

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
     * Devuelve el ranking de técnicos (diario y mensual).
     */
    public function getRankings(Request $request)
    {
        // Ranking Diario
        $daily = User::whereHas('roles', fn($q) => $q->where('name', 'tecnico'))
            ->withCount([
                'ordenes as count' => function ($query) {
                    $query->where('estado_orden', 'ejecutada')
                        ->whereDate('fecha_fin_atencion', today());
                }
            ])
            ->orderByDesc('count')
            ->take(10)
            ->get()
            ->map(fn($user) => ['name' => $user->name, 'count' => $user->count]);

        // Ranking Mensual
        $monthly = User::whereHas('roles', fn($q) => $q->where('name', 'tecnico'))
            ->withCount([
                'ordenes as count' => function ($query) {
                    $query->where('estado_orden', 'ejecutada')
                        ->whereMonth('fecha_fin_atencion', now()->month)
                        ->whereYear('fecha_fin_atencion', now()->year);
                }
            ])
            ->orderByDesc('count')
            ->take(10)
            ->get()
            ->map(fn($user) => ['name' => $user->name, 'count' => $user->count]);

        return response()->json([
            'daily' => $daily,
            'monthly' => $monthly,
        ]);
    }
}