<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'administrador']);
        Role::firstOrCreate(['name' => 'operador']);
        Role::firstOrCreate(['name' => 'tecnico']);
        Role::firstOrCreate(['name' => 'cliente']);
    }
}