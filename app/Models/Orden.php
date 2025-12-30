<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    protected $fillable = [
        'numero_orden',
        'cliente_id',
        'direccion',
        'cedula',
        'cedula',
        'precinto',
        'nombre_cliente',
        
        'tipo_orden',
        'tipo_funcion',
        'fecha_trn',
        'fecha_vencimiento',
        'estado_orden',
        'tipo',
        'estado_interno',
        
        'direccion_asociado',
        'telefono',
        'saldo_cliente',
        'solicitado_por',
        'estado_tv',
        
        'technician_id', // tecnico_principal
        'tecnico_auxiliar_id',
        'solicitud_suscriptor',
        'solucion_tecnico',
        
        'valor_total',
        'observaciones',
        'articulos', // JSON Repeater
        
        'status', 
        
        // Tracking timestamps
        'fecha_asignacion',
        'fecha_inicio_atencion',
        'fecha_fin_atencion',
        'fecha_cierre',
    ];

    protected $casts = [
        'fecha_trn' => 'date',
        'fecha_vencimiento' => 'date',
        'articulos' => 'array',
        'saldo_cliente' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'fecha_asignacion' => 'datetime',
        'fecha_inicio_atencion' => 'datetime',
        'fecha_fin_atencion' => 'datetime',
        'fecha_cierre' => 'datetime',
        'fecha_llegada' => 'datetime',
    ];



    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_ASIGNADA = 'asignada';
    const ESTADO_EN_SITIO = 'en_sitio';
    const ESTADO_EN_PROCESO = 'en_proceso';
    const ESTADO_EJECUTADA = 'ejecutada';
    const ESTADO_CERRADA = 'cerrada';
    const ESTADO_ANULADA = 'anulada';

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function tecnicoAuxiliar()
    {
        return $this->belongsTo(User::class, 'tecnico_auxiliar_id');
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
