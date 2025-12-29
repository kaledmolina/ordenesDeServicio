<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdenFotoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'orden_id' => $this->orden_id,
            // Se mantiene la ruta relativa por si se necesita
            'path' => $this->path, 
            // 👇 NUEVO CAMPO: Se añade la URL completa y funcional para ver la foto
            'url' => route('fotos.show', ['ordenFoto' => $this->id]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}