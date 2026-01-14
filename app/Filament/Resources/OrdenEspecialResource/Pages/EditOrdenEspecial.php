<?php

namespace App\Filament\Resources\OrdenEspecialResource\Pages;

use App\Filament\Resources\OrdenEspecialResource;
use Filament\Resources\Pages\EditRecord;

class EditOrdenEspecial extends EditRecord
{
    protected static string $resource = OrdenEspecialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No delete action needed by default, view only context mostly
        ];
    }

    // Force read-only mode implicitly via resource form, but we can also disable form actions
    protected function getFormActions(): array
    {
        return [];
    }
}
