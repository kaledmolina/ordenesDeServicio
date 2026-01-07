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
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200" wire:loading wire:target="callMountedAction">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700 overflow-hidden relative">
                                        <div class="bg-primary-600 h-4 rounded-full absolute top-0 left-0 w-full animate-pulse"></div>
                                        <div class="absolute top-0 left-0 w-full h-full bg-[linear-gradient(45deg,rgba(255,255,255,0.2)_25%,transparent_25%,transparent_50%,rgba(255,255,255,0.2)_50%,rgba(255,255,255,0.2)_75%,transparent_75%,transparent)] bg-[length:1rem_1rem] animate-[spin_1s_linear_infinite]"></div> 
                                    </div>
                                    <span class="text-sm font-medium text-gray-600 animate-pulse">Importando clientes, esto puede tardar un momento...</span>
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
