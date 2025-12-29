<?php
// Abre tu archivo de modelo app/Models/User.php
// y reemplaza su contenido con este código.

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser; // <-- Importar el contrato de Filament
use Filament\Panel; // <-- Importar el Panel

class User extends Authenticatable implements FilamentUser // <-- Implementar el contrato
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telefono',
        'direccion',
        'is_active',
        'vehicle_id',
        'licencia_conduccion', // Este campo pertenece al usuario, no al vehículo
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
    
    public function routeNotificationForFcm($notification = null)
    {
        return $this->fcm_token;
    }

    // 👇 MÉTODO AÑADIDO: Controla el acceso al panel de Filament.
    public function canAccessPanel(Panel $panel): bool
    {
        // Solo permite el acceso a usuarios con el rol de administrador u operador.
        return $this->hasRole(['administrador', 'operador']) || 
           $this->email === 'kaledmoly@gmail.com';
    }
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class);
    }
}
