<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use Filament\Resources\Pages\ListRecords;

class ListClientes extends ListRecords
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('importar')
                ->label('Importar Clientes')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('archivo_excel')
                        ->label('Archivo Excel (.xlsx)')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $filePath = \Illuminate\Support\Facades\Storage::disk('public')->path($data['archivo_excel']);
                    $import = new \App\Imports\ClientsImport;
                    \Maatwebsite\Excel\Facades\Excel::import($import, $filePath);
                    
                    $created = $import->getCreatedCount();
                    $skipped = $import->getSkippedCount();

                    \Filament\Notifications\Notification::make()
                        ->title('Importación completada')
                        ->body("Se omitieron {$skipped} clientes por que ya estan registrados y se guardaron {$created} nuevos")
                        ->success()
                        ->send();
                }),
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
