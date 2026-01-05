<?php

namespace App\Filament\Resources\SeguimientoTecnicoResource\Pages;

use App\Filament\Resources\SeguimientoTecnicoResource;
use Filament\Resources\Pages\ListRecords;

class ListSeguimientoTecnicos extends ListRecords
{
    protected static string $resource = SeguimientoTecnicoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
