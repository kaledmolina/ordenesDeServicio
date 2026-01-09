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
        'precinto',
        'nombre_cliente',

        'tipo_orden',
        'tipo_funcion',
        'fecha_trn',
        'fecha_vencimiento',
        'estado_orden',
        'tipo',
        'estado_interno',
        'clasificacion',

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

        // 'status', REMOVED 

        // Tracking timestamps
        'fecha_asignacion',
        'fecha_inicio_atencion',
        'fecha_fin_atencion',
        'fecha_cierre',
        'fecha_llegada',
        'mac_router',
        'mac_bridge',
        'mac_ont',
        'otros_equipos',
        'firma_tecnico',
        'firma_suscriptor',
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



    const TIPO_ORDEN_OPTIONS = [
        '025' => '025 REVISION TECNICA',
        '037' => '037 CAMBIO CONTRASEÑA',
    ];

    const SOLICITUD_SUSCRIPTOR_OPTIONS = [
        '1' => '1 SERVICIO INTERMITENTE',
        '2' => '2 SIN SERVICIO DE INTERNET',
        '3' => '3 SIN ALCANCE POTENCIA',
        '4' => '4 SERVICIO LENTO',
        '5' => '5 SIN SERVICIO DE TELEVISION',
        '132' => '132 CAMBIO DE CUENTA EN WIM',
        '133' => '133 CAMBIO DE EQUIPO',
        '136' => '136 LUZ ROJA',
        '137' => '137 MANTENIMIENTO CORRECTIVO',
        '139' => '139 INSTALACION AUTOMONITOREO',
        '140' => '140 REVISION TCA AUTOMONITOREO',
        '142' => '142 GARANTIA INTERNET',
        '143' => '143 GARANTIA TV',
        '144' => '144 GARANTIA TV E INTERNET',
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

    public function feedback()
    {
        return $this->hasOne(OrderFeedback::class);
    }

    public function getRouteKeyName()
    {
        return 'numero_orden';
    }
}
