<?php

namespace App\Filament\Resources\SeguimientoTecnicoResource\Pages;

use App\Filament\Resources\SeguimientoTecnicoResource;
use Filament\Resources\Pages\EditRecord;

class EditSeguimientoTecnico extends EditRecord
{
    protected static string $resource = SeguimientoTecnicoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
