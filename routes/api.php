<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Models\OrdenFoto;

use App\Http\Controllers\Api\V1\FcmTokenController;

// --- Rutas Públicas v1 ---
// Esta ruta no necesita autenticación.
Route::post('/v1/login', [AuthController::class, 'login']);

// Ruta para verificar que la API está funcionando.
Route::get('/v1/health', function () {
    return response()->json(['status' => 'ok']);
});


// Todas las rutas dentro de este grupo requieren un token de autenticación válido.
// --- Rutas Protegidas v1 ---
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/update-fcm-token', [FcmTokenController::class, 'store']);

    Route::get('/me', [AuthController::class, 'me']);
    // Rutas para las Órdenes
    Route::get('/orders/{orden}/photos', [OrderController::class, 'getPhotos']);
    Route::get('/private-fotos/{ordenFoto}', [OrderController::class, 'showPhoto']); 
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{orden:numero_orden}', [OrderController::class, 'show']);
    Route::post('/orders/{orden:numero_orden}/accept', [OrderController::class, 'acceptOrder']);
    Route::post('/orders/{orden:numero_orden}/close', [OrderController::class, 'closeOrder']);
    Route::post('/orders/{orden:numero_orden}/reject', [OrderController::class, 'rejectOrder']);
    Route::post('/orders/{orden:numero_orden}/update-details', [OrderController::class, 'updateDetails']); 
    Route::post('/orders/{orden:numero_orden}/upload-photo', [OrderController::class, 'uploadPhoto']);




});