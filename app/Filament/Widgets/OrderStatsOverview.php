<?php

namespace App\Filament\Widgets;

use App\Models\Orden;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $isTechnician = !$user->hasAnyRole(['administrador', 'operador']);

        $query = Orden::query();
        if ($isTechnician) {
            $query->where('technician_id', $user->id);
        }

        return [
            Stat::make('Órdenes Pendientes', (clone $query)->where('estado_orden', Orden::ESTADO_PENDIENTE)->count())
                ->description('Órdenes nuevas sin asignar')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('gray')
                ->url(route('filament.admin.pages.dashboard', ['status' => Orden::ESTADO_PENDIENTE])),

            Stat::make('Órdenes Asignadas', (clone $query)->where('estado_orden', Orden::ESTADO_ASIGNADA)->count())
                ->description('Asignadas a un técnico')
                ->color('warning')
                ->url(route('filament.admin.pages.dashboard', ['status' => Orden::ESTADO_ASIGNADA])),

            Stat::make('Órdenes en Proceso', (clone $query)->where('estado_orden', Orden::ESTADO_EN_PROCESO)->count())
                ->description('En atención por técnico')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info')
                ->url(route('filament.admin.pages.dashboard', ['status' => Orden::ESTADO_EN_PROCESO])),

            Stat::make('Órdenes Ejecutadas', (clone $query)->where('estado_orden', Orden::ESTADO_EJECUTADA)->count())
                ->description('Finalizadas, pendiente de cierre')
                ->color('success')
                ->url(route('filament.admin.pages.dashboard', ['status' => Orden::ESTADO_EJECUTADA])),

            Stat::make('Órdenes Cerradas', (clone $query)->where('estado_orden', Orden::ESTADO_CERRADA)->count())
                ->description('Cerradas administrativamente')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('success')
                ->url(route('filament.admin.pages.dashboard', ['status' => Orden::ESTADO_CERRADA])),

            Stat::make('Órdenes Anuladas', (clone $query)->where('estado_orden', Orden::ESTADO_ANULADA)->count())
                ->color('danger')
                ->url(route('filament.admin.pages.dashboard', ['status' => Orden::ESTADO_ANULADA])),
        ];
    }
}
