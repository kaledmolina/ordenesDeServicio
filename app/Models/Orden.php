<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Orden extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function booted()
    {
        static::creating(function ($orden) {
            if (empty($orden->numero_orden)) {
                $orden->numero_orden = (static::max('numero_orden') ?? 0) + 1;
            }
        });
    }

    protected $appends = ['deadline_at'];

    // Colombian Holidays (Hardcoded for 2025-2028 - Update annually or move to DB)
    const HOLIDAYS = [
        // 2025
        '2025-01-01',
        '2025-01-06',
        '2025-03-24',
        '2025-04-17',
        '2025-04-18',
        '2025-05-01',
        '2025-06-02',
        '2025-06-23',
        '2025-06-30',
        '2025-07-20',
        '2025-08-07',
        '2025-08-18',
        '2025-10-13',
        '2025-11-03',
        '2025-11-17',
        '2025-12-08',
        '2025-12-25',
        // 2026
        '2026-01-01',
        '2026-01-12',
        '2026-03-23',
        '2026-04-02',
        '2026-04-03',
        '2026-05-01',
        '2026-05-18',
        '2026-06-08',
        '2026-06-15',
        '2026-06-29',
        '2026-07-20',
        '2026-08-07',
        '2026-08-17',
        '2026-10-12',
        '2026-11-02',
        '2026-11-16',
        '2026-12-08',
        '2026-12-25',
        // 2027
        '2027-01-01',
        '2027-01-11',
        '2027-03-22',
        '2027-03-25',
        '2027-03-26',
        '2027-05-01',
        '2027-05-10',
        '2027-05-31',
        '2027-06-07',
        '2027-07-05',
        '2027-07-20',
        '2027-08-07',
        '2027-08-16',
        '2027-10-18',
        '2027-11-01',
        '2027-11-15',
        '2027-12-08',
        '2027-12-25',
        // 2028
        '2028-01-01',
        '2028-01-10',
        '2028-03-20',
        '2028-04-13',
        '2028-04-14',
        '2028-05-01',
        '2028-05-29',
        '2028-06-19',
        '2028-06-26',
        '2028-07-03',
        '2028-07-20',
        '2028-08-07',
        '2028-08-21',
        '2028-10-16',
        '2028-11-06',
        '2028-11-13',
        '2028-12-08',
        '2028-12-25',
    ];

    public function getDeadlineAtAttribute()
    {
        if (!$this->created_at)
            return null;

        $date = $this->created_at->copy();
        $hoursToAdd = 48;

        // Loop hour by hour to skip non-business hours? 
        // User asked for "48 hours" but usually implies "2 Business Days".
        // Simpler approach: Add 2 days, but if lands on weekend/holiday, push forward.
        // OR: Count 48 'business hours'? Usually strictly 2 days excluding weekends.

        // Let's implement: Add 2 days worth of business time.
        // If created Friday 4PM, +48h -> Sunday 4PM.
        // Excluding weekends: Friday 4PM -> Monday 4PM (24h) -> Tuesday 4PM (48h).

        $addedDays = 0;
        while ($addedDays < 2) {
            $date->addDay();
            // Check if $date is weekend or holiday
            $ymd = $date->format('Y-m-d');
            $isWeekend = $date->isWeekend(); // Saturday or Sunday
            $isHoliday = in_array($ymd, self::HOLIDAYS);

            if (!$isWeekend && !$isHoliday) {
                $addedDays++;
            }
        }

        return $date;
    }

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

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
        'novedades_noc',
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
        'fecha_llegada' => 'datetime',
        // 'solucion_tecnico' => 'array', // Removed strict cast to handle legacy data
    ];

    // Accessor to safely decode JSON or return legacy string as single-item array
    public function getSolucionTecnicoAttribute($value)
    {
        if (is_null($value))
            return [];
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [$value];
    }

    // Mutator to always save as JSON
    public function setSolucionTecnicoAttribute($value)
    {
        $this->attributes['solucion_tecnico'] = is_array($value) ? json_encode($value) : $value;
    }

    const TIPO_ORDEN_OPTIONS = [
        '025' => '025 REVISION TECNICA',
        '037' => '037 CAMBIO CONTRASEÑA',
    ];

    const SOLUCION_TECNICO_OPTIONS = [
        '1 CAMBIO - CONECTOR MECÁNICO PARTIDO' => '1 CAMBIO - CONECTOR MECÁNICO PARTIDO',
        '2 CAIDA GENERAL - PROVEEDOR' => '2 CAIDA GENERAL - PROVEEDOR',
        '3 CAIDA GENERAL - CABECERA' => '3 CAIDA GENERAL - CABECERA',
        '4 CONFIGURACIÓN DE EQUIPO' => '4 CONFIGURACIÓN DE EQUIPO',
        '5 FIBRA PARTIDA - SE INSTALA ROSETA' => '5 FIBRA PARTIDA - SE INSTALA ROSETA',
        '6 FIBRA PARTIDA - CAMBIO FIBRA' => '6 FIBRA PARTIDA - CAMBIO FIBRA',
        '7 CAMBIO -EQUIPO DEFECTUOSO' => '7 CAMBIO -EQUIPO DEFECTUOSO',
        '8 CAMBIO -EQUIPO QUEMADO' => '8 CAMBIO -EQUIPO QUEMADO',
        '9 CAMBIO- EQUIPO MAL USO USUARIO' => '9 CAMBIO- EQUIPO MAL USO USUARIO',
        '10 REFRESH DE MEGAS' => '10 REFRESH DE MEGAS',
        '11 CAMBIO CONECTOR DE CAJA' => '11 CAMBIO CONECTOR DE CAJA',
        '12 ENFRENTADOR DE CAJA' => '12 ENFRENTADOR DE CAJA',
        '13 FIBRA ATENUADA' => '13 FIBRA ATENUADA',
        '14 RETENCION DE FIBRA' => '14 RETENCION DE FIBRA',
        '15 USUARIO SUSPENDIDO' => '15 USUARIO SUSPENDIDO',
        '16 CAMBIO CONECTOR RF' => '16 CAMBIO CONECTOR RF',
        '17 ESCANEO DE CANALES' => '17 ESCANEO DE CANALES',
        '18 CAMBIO NOMBRE WINBOX' => '18 CAMBIO NOMBRE WINBOX',
        '19 MEJORA DE POTENCIA' => '19 MEJORA DE POTENCIA',
        '20 DAÑO MASIVO' => '20 DAÑO MASIVO',
        '21 CONFIGURACIÓN DE TV' => '21 CONFIGURACIÓN DE TV',
        '22 FIBRA PARTIDA- USO DE RESERVA' => '22 FIBRA PARTIDA- USO DE RESERVA',
        '23 SERVICIO OPERATIVO' => '23 SERVICIO OPERATIVO',
        '24 CAMBIO DE BRIDGE' => '24 CAMBIO DE BRIDGE',
        '25 CAMBIO DE CAMARA' => '25 CAMBIO DE CAMARA',
        '26 REUBICACION DE FIBRA' => '26 REUBICACION DE FIBRA',
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
