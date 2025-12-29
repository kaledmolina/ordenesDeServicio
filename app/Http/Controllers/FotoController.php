<?php

namespace App\Http\Controllers;

use App\Models\OrdenFoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FotoController extends Controller
{
    /**
     * Busca una foto en el almacenamiento privado y la devuelve como una respuesta de archivo.
     */
    public function show(OrdenFoto $ordenFoto)
    {
        // Verifica que el archivo exista en el disco 'local'
        if (!Storage::disk('local')->exists($ordenFoto->path)) {
            abort(404);
        }

        // Devuelve el archivo con el tipo de contenido correcto (ej. image/jpeg)
        return Storage::disk('local')->response($ordenFoto->path);
    }
}
