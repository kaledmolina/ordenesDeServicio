<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    protected $fillable = [
        'numero_orden',
        'numero_expediente',
        'nombre_cliente',
        'fecha_hora',
        'valor_servicio',
        'placa',
        'referencia',
        'nombre_asignado',
        'celular',
        'unidad_negocio',
        'movimiento',
        'servicio',
        'modalidad',
        'tipo_activo',
        'marca',
        'ciudad_origen',
        'direccion_origen',
        'observaciones_origen',
        'ciudad_destino',
        'direccion_destino',
        'observaciones_destino',
        'observaciones_generales',
        'es_programada',
        'fecha_programada',
        'technician_id',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
            'fecha_programada' => 'datetime',
            'es_programada' => 'boolean',
            'valor_servicio' => 'decimal:2',
        ];
    }
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function fotos()
    {
        return $this->hasMany(OrdenFoto::class);
    }

    public function getRouteKeyName()
    {
        return 'numero_orden';
    }
}
