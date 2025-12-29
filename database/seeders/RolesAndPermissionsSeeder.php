<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['name' => 'administrador']);
        Role::create(['name' => 'operador']);
        Role::create(['name' => 'tecnico']);
    }
}