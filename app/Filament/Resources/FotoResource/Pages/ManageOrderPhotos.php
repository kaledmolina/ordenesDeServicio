<?php
namespace App\Filament\Resources\FotoResource\Pages;

use App\Filament\Resources\FotoResource;
use App\Models\Orden;
use App\Models\OrdenFoto;
use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;

class ManageOrderPhotos extends Page
{
    protected static string $resource = FotoResource::class;

    protected static string $view = 'filament.resources.foto-resource.pages.manage-order-photos';

    public Orden $record;

    public function getTitle(): string
    {
        return "Fotos de la Orden #" . $this->record->numero_orden;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('uploadPhotos')
                ->label('Subir Nuevas Fotos')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('fotos_nuevas')
                        ->label('Seleccionar Fotos')
                        ->multiple()
                        ->image()
                        ->disk('local')
                        ->directory('private/orden-fotos')
                        ->required(),
                ])
                ->action(function (array $data) {
                    foreach ($data['fotos_nuevas'] as $fotoPath) {
                        $this->record->fotos()->create(['path' => $fotoPath]);
                    }
                    Notification::make()->title('Fotos subidas exitosamente.')->success()->send();
                }),
        ];
    }

    // Acción para ver la foto en un modal
    public function viewPhotoAction(): Action
    {
        return Action::make('viewPhotoAction')
            ->modalContent(fn (array $arguments): \Illuminate\Contracts\View\View => 
                view('filament.modals.view-photo', ['photo' => OrdenFoto::find($arguments['photoId'])])
            )
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Cerrar');
    }

    // Acción para eliminar la foto
    public function deletePhotoAction(): Action
    {
        return Action::make('deletePhoto')
            ->label('Eliminar') // El texto que se mostrará en el botón
            ->icon('heroicon-s-trash')
            ->color('danger')
            ->button() // CAMBIO: Renderiza como un botón completo
            ->size('xs') // CAMBIO: Hace el botón más pequeño y compacto
            ->requiresConfirmation()
            ->modalHeading('Eliminar Foto')
            ->modalDescription('¿Está seguro de que desea eliminar esta foto?')
            ->modalSubmitActionLabel('Sí, eliminar')
            ->action(function (array $arguments) {
                $foto = OrdenFoto::find($arguments['photoId']);
                if ($foto) {
                    $foto->delete(); 
                }
                Notification::make()->title('Foto eliminada.')->success()->send();
            });
    }
}