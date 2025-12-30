<?php

namespace App\Filament\Widgets;

use App\Models\Orden;
use App\Models\User;
use Filament\Widgets\Widget;

class OperatorTrackingWidget extends Widget
{
    protected static string $view = 'filament.widgets.operator-tracking-widget';
    
    // Optional: make it full width if needed, or sort order
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['administrador', 'operador']);
    }

    protected function getViewData(): array
    {
        // Fetch operators (technicians)
        $operators = User::role('tecnico')
            ->get()
            ->map(function ($user) {
                // Find active order for this user
                $user->activeOrder = Orden::where('technician_id', $user->id)
                    ->where('estado_orden', Orden::ESTADO_EN_PROCESO)
                    ->latest('fecha_inicio_atencion')
                    ->first();
                return $user;
            });

        // Fetch unassigned orders
        $unassignedOrders = Orden::where('estado_orden', Orden::ESTADO_PENDIENTE)
            ->orderBy('created_at', 'asc')
            ->limit(5) // Limit to 5 for the dashboard widget
            ->get();

        return [
            'operators' => $operators,
            'unassignedOrders' => $unassignedOrders,
        ];
    }
}
