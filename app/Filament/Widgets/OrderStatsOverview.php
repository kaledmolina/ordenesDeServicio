<?php

namespace App\Filament\Widgets;

use App\Models\Orden;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Órdenes Abiertas', Orden::where('status', 'abierta')->count())
                ->description('Órdenes nuevas sin asignar')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                // Al hacer clic, se redirige al dashboard con un filtro
                ->url(route('filament.admin.pages.dashboard', ['status' => 'abierta'])),

            Stat::make('Órdenes en Proceso', Orden::where('status', 'en proceso')->count())
                ->description('Órdenes que un técnico está atendiendo')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning')
                ->chart([10, 5, 2, 8, 12, 6, 19])
                ->url(route('filament.admin.pages.dashboard', ['status' => 'en proceso'])),
                
            Stat::make('Órdenes Cerradas', Orden::where('status', 'cerrada')->count())
                ->description('Servicios finalizados exitosamente')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('primary')
                ->chart([17, 12, 10, 15, 5, 8, 3])
                ->url(route('filament.admin.pages.dashboard', ['status' => 'cerrada'])),

            Stat::make('Órdenes Programadas', Orden::where('status', 'programada')->count())
                ->color('info')
                ->url(route('filament.admin.pages.dashboard', ['status' => 'programada'])),
                
            Stat::make('Órdenes Fallidas', Orden::where('status', 'fallida')->count())
                ->color('danger')
                ->url(route('filament.admin.pages.dashboard', ['status' => 'fallida'])),

            Stat::make('Órdenes Anuladas', Orden::where('status', 'anulada')->count())
                ->color('gray')
                ->url(route('filament.admin.pages.dashboard', ['status' => 'anulada'])),
        ];
    }
}
