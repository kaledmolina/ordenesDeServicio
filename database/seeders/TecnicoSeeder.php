<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class TecnicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tecnicoRole = Role::firstOrCreate(['name' => 'tecnico']);

        $user = User::firstOrCreate(
            ['email' => 'tecnico@test.com'],
            [
                'name' => 'Técnico Test',
                'password' => Hash::make('password'),
                'telefono' => null,
                'direccion' => null,
                'vehicle_id' => null,
                'licencia_conduccion' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if (!$user->hasRole($tecnicoRole->name)) {
            $user->assignRole($tecnicoRole);
        }
    }
}

