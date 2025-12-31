<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FotoController;
use App\Http\Controllers\PdfController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/private-fotos/{ordenFoto}', [FotoController::class, 'show'])
    ->middleware('auth')
    ->name('fotos.show');
Route::get('/orden/{orden}/pdf', [PdfController::class, 'downloadOrdenPdf'])
    ->middleware('auth')
    ->name('orden.pdf.download');

Route::get('/orden/{orden}/stream-pdf', [PdfController::class, 'streamOrdenPdf'])
    ->middleware('auth')
    ->name('orden.pdf.stream');
