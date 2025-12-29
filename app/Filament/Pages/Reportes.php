<?php
// Abre el archivo app/Filament/Pages/Reportes.php
// y reemplaza su contenido con este código.

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdenesExport;
use Filament\Actions\Action;

class Reportes extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';
    protected static string $view = 'filament.pages.reportes';
    protected static ?string $navigationLabel = 'Reportes';
    protected static ?string $title = 'Generar Reportes de Órdenes';
    protected static ?string $navigationGroup = 'Herramientas';
    protected static ?int $navigationSort = 4;

    // Propiedad para guardar los datos del formulario (fechas)
    public ?array $data = [];

    // Se ejecuta al cargar la página para inicializar el formulario
    public function mount(): void
    {
        $this->form->fill();
    }

    // Define la estructura del formulario que se mostrará en la página
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('fecha_inicio')
                    ->label('Fecha de Inicio')
                    ->required(),
                DatePicker::make('fecha_fin')
                    ->label('Fecha de Fin')
                    ->required(),
            ])
            ->statePath('data');
    }

    // Esta es la acción que se ejecutará al presionar el botón de descarga
    public function export()
    {
        $data = $this->form->getState();
        $startDate = $data['fecha_inicio'];
        $endDate = $data['fecha_fin'];
        
        $fileName = "reporte-ordenes-{$startDate}-a-{$endDate}.xlsx";

        // Llama a la clase de exportación y descarga el archivo
        return Excel::download(new OrdenesExport($startDate, $endDate), $fileName);
    }
}
