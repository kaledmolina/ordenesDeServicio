<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $user = $request->user();

        // Solo los técnicos pueden iniciar sesión en la API móvil
        if (!$user->hasRole('tecnico')) {
             Auth::logout(); // Cerramos la sesión si no es técnico
             return response()->json(['message' => 'Acceso no autorizado.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function user(Request $request)
    {
        return $request->user();
    }
    
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada exitosamente.']);
    }
    
    public function updateFcmToken(Request $request)
    {
        $request->validate(['fcm_token' => 'required']);
        $request->user()->update(['fcm_token' => $request->fcm_token]);
        return response()->json(['message' => 'Token actualizado.']);
    }

    public function profile(Request $request)
    {
        // Carga la relación 'vehicle' para incluirla en la respuesta JSON a
        return response()->json($request->user()->load('vehicle'));
    }
}