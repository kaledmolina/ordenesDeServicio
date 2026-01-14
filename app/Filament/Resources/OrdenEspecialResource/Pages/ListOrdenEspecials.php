<?php

namespace App\Filament\Resources\OrdenEspecialResource\Pages;

use App\Filament\Resources\OrdenEspecialResource;
use Filament\Resources\Pages\ListRecords;

class ListOrdenEspecials extends ListRecords
{
    protected static string $resource = OrdenEspecialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action needed
        ];
    }
}
