<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'telefono',
        'direccion',
        'is_active',
        'codigo_contrato',
        
        // Billing Fields
        'estrato',
        'zona',
        'barrio',
        'telefono_facturacion',
        'otro_telefono',
        'tipo_servicio',
        'vendedor',
        'tipo_operacion',
        'suscripcion_tv',
        'suscripcion_internet',
        'fecha_ultimo_pago',
        'estado_tv',
        'estado_internet',
        'saldo_tv',
        'saldo_internet',
        'saldo_otros',
        'saldo_total',
        'tarifa_tv',
        'tarifa_internet',
        'tarifa_total',
        'plan_internet',
        'velocidad',
        'cortado_tv',
        'retiro_tv',
        'cortado_int',
        'retiro_int',
        'serial',
        'mac',
        'ip',
        'marca',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            
            'suscripcion_tv' => 'date',
            'suscripcion_internet' => 'date',
            'fecha_ultimo_pago' => 'date',
            'saldo_tv' => 'decimal:2',
            'saldo_internet' => 'decimal:2',
            'saldo_otros' => 'decimal:2',
            'saldo_total' => 'decimal:2',
            'tarifa_tv' => 'decimal:2',
            'tarifa_internet' => 'decimal:2',
            'tarifa_total' => 'decimal:2',
        ];
    }
    
    public function routeNotificationForFcm($notification = null)
    {
        return $this->fcm_token;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole(['administrador', 'operador', 'tecnico']) || 
           $this->email === 'kaledmoly@gmail.com';
    }

    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class);
    }

    public function ordenes()
    {
        return $this->hasMany(Orden::class, 'technician_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(OrderFeedback::class, 'technician_id');
    }
}
