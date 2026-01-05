<?php

namespace App\Http\Controllers;

use App\Models\OrderFeedback;
use App\Models\Orden;
use Illuminate\Http\Request;

class OrderFeedbackController extends Controller
{
    public function store(Request $request, Orden $orden)
    {
        // 1. Validar inputs
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            
            'arrived_on_time' => 'required|boolean',
            'is_friendly' => 'required|boolean',
            'problem_solved' => 'required|boolean',
            'wears_uniform' => 'required|boolean',
            'left_clean' => 'required|boolean',

            'comment' => 'nullable|string|max:1000',
        ]);

        // 2. Verificar que la orden esté ejecutada
        if ($orden->estado_orden !== Orden::ESTADO_EJECUTADA) {
             return response()->json([
                'success' => false,
                'message' => 'Solo se pueden calificar órdenes ejecutadas.'
            ], 403);
        }

        // 3. (Opcional) Verificar si ya existe feedback para evitar duplicados
        if ($orden->feedback()->exists()) {
             return response()->json([
                'success' => false,
                'message' => 'Esta orden ya fue calificada.'
            ], 422);
        }

        // 4. Crear el feedback
        $orden->feedback()->create([
            'technician_id' => $orden->technician_id,
            'rating' => $validated['rating'],
            
            'arrived_on_time' => $validated['arrived_on_time'],
            'is_friendly' => $validated['is_friendly'],
            'problem_solved' => $validated['problem_solved'],
            'wears_uniform' => $validated['wears_uniform'],
            'left_clean' => $validated['left_clean'],
            
            'comment' => $validated['comment'],
        ]);

        return response()->json(['success' => true, 'message' => '¡Gracias por calificar nuestro servicio!']);
    }
}
