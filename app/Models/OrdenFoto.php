<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;


class OrdenFoto extends Model
{
    use HasFactory;
    protected $fillable = ['orden_id', 'path'];

    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }
    protected static function booted(): void
    {
        // Esto se ejecuta automáticamente cuando un registro de OrdenFoto se está eliminando.
        static::deleting(function (OrdenFoto $ordenFoto) {
            // Borra el archivo físico del almacenamiento privado.
            Storage::disk('local')->delete($ordenFoto->path);
        });
    }
}
