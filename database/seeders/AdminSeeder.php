<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'administrador']);
        $operadorRole = Role::firstOrCreate(['name' => 'operador']);
        $tecnicoRole = Role::firstOrCreate(['name' => 'tecnico']);

        $user = User::firstOrCreate(
            ['email' => 'kaledmoly@gmail.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'telefono' => null,
                'direccion' => null,
                'vehicle_id' => null,
                'licencia_conduccion' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if (! $user->hasRole($adminRole->name)) {
            $user->assignRole($adminRole);
        }
        if (! $user->hasRole($operadorRole->name)) {
            $user->assignRole($operadorRole);
        }
        if (! $user->hasRole($tecnicoRole->name)) {
            $user->assignRole($tecnicoRole);
        }
    }
}



