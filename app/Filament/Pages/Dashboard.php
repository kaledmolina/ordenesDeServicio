<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
// 👇 Importa los widgets que vas a usar
use App\Filament\Widgets\OrderStatsOverview;
use App\Filament\Widgets\FilteredOrdersTable;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.dashboard';
    protected static ?string $title = 'Dashboard';

    // Propiedad para recibir el status desde la URL
    public ?string $status = 'todas';

    // El método mount se ejecuta al cargar la página
    public function mount(): void
    {
        // Tomamos el valor 'status' de la URL, si no existe, por defecto es 'todas'
        $this->status = request()->query('status', 'todas');
    }

    // 👇 MÉTODO AÑADIDO: Registra los widgets que se usarán en esta página.
    public function getWidgets(): array
    {
        return [
            OrderStatsOverview::class,
            FilteredOrdersTable::class,
        ];
    }
}
