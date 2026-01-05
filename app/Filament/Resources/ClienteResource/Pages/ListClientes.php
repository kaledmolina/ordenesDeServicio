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
                    $file = public_path('storage/' . $data['archivo_excel']);
                    \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\ClientsImport, $file);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Importación completada')
                        ->body('Los clientes han sido importados exitosamente.')
                        ->success()
                        ->send();
                }),
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
