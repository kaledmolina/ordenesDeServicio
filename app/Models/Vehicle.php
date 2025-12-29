<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Vehicle extends Model
{
    use HasFactory;
    protected $fillable = [
        'placa', 
        'modelo', 
        'marca',
        'tarjeta_propiedad',
        'fecha_tecnomecanica',
        'fecha_soat',
        'mantenimiento_preventivo_taller',
        'fecha_mantenimiento',
        'fecha_ultimo_aceite',
    ];

    // Se definen los casts para los campos de fecha
    protected $casts = [
        'fecha_tecnomecanica' => 'date',
        'fecha_soat' => 'date',
        'fecha_mantenimiento' => 'date',
        'fecha_ultimo_aceite' => 'date',
    ];


    public function user()
    {
        return $this->hasOne(User::class);
    }
}
