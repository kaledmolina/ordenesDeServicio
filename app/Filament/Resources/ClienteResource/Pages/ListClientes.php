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
                ->form([
                    \Filament\Forms\Components\FileUpload::make('archivo_excel')
                        ->label('Archivo Excel (.xlsx)')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->required(),
                    \Filament\Forms\Components\Placeholder::make('loading')
                        ->label('')
                        ->content(new \Illuminate\Support\HtmlString('
                            <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 transition-colors duration-300" wire:loading wire:target="callMountedAction">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <svg class="animate-spin h-8 w-8 text-primary-600 dark:text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300 animate-pulse">
                                        Importando clientes, esto puede tardar un momento...
                                    </span>
                                </div>
                            </div>
                        ')),
                ])
                ->action(function (array $data) {
                    set_time_limit(600); // 10 minutes for large files
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
