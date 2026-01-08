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
                ->modalDescription('El proceso puede tardar varios minutos dependiendo del tamaño del archivo. Por favor, no cierre la ventana.')
                ->modalCloseButton(false)
                ->closeModalByClickingAway(false)
                ->disabled(fn() => \Illuminate\Support\Facades\Cache::has('import_progress_' . auth()->id()))
                ->form([
                    \Filament\Forms\Components\FileUpload::make('archivo_excel')
                        ->label('Archivo Excel (.xlsx)')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->required(),
                    // Placeholder removed
                ])
                ->action(function (array $data) {
                    // set_time_limit(600); // Removed, using queue
                    $filePath = \Illuminate\Support\Facades\Storage::disk('public')->path($data['archivo_excel']);

                    // Clear any previous cancellation flags or progress
                    \Illuminate\Support\Facades\Cache::forget('import_cancelled_' . auth()->id());
                    \Illuminate\Support\Facades\Cache::forget('import_progress_' . auth()->id());

                    $import = new \App\Imports\ClientsImport(auth()->user());
                    \Maatwebsite\Excel\Facades\Excel::queueImport($import, $filePath);

                    \Filament\Notifications\Notification::make()
                        ->title('Importación iniciada')
                        ->body("La importación de clientes ha comenzado en segundo plano. Recibirá una notificación cuando termine (si está configurado).")
                        ->success()
                        ->send();
                }),
            \Filament\Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\ClienteResource\Widgets\ImportProgress::class,
        ];
    }
}
