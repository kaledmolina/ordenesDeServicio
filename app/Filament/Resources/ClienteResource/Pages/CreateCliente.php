<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCliente extends CreateRecord
{
    protected static string $resource = ClienteResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Create the user record
        $user = static::getModel()::create($data);
        
        // Assign the 'cliente' role
        $user->assignRole('cliente');

        return $user;
    }
}
