<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\User;
use App\Models\Orden;
use Illuminate\Database\Eloquent\Builder;

class TechnicianLocationTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    // Optional polling to keep locations fresh
    protected static ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        // Only visible if NO status filter is active
        return empty(request()->query('status'));
    }

    protected function getTableHeading(): string
    {
        return 'Ubicación Reciente de Técnicos';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereHas('roles', fn($q) => $q->where('name', 'tecnico'))
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Técnico')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('last_location')
                    ->label('Última Ubicación / Actividad')
                    ->getStateUsing(function ($record) {
                        // Find latest order involved in active work
                        // Statuses: 'en_sitio', 'en_proceso' (active) or 'ejecutada' (recently done)
                        // We check active first, then executed.
            
                        $latestOrder = Orden::where('technician_id', $record->id)
                            ->whereIn('estado_orden', [
                                Orden::ESTADO_EN_SITIO,
                                Orden::ESTADO_EN_PROCESO,
                                Orden::ESTADO_EJECUTADA
                            ])
                            ->latest('updated_at') // Most recently updated
                            ->with('cliente')
                            ->first();

                        if (!$latestOrder) {
                            return 'Sin actividad reciente';
                        }

                        $barrio = $latestOrder->cliente->barrio ?? 'Sin barrio';
                        $statusRaw = $latestOrder->estado_orden;

                        // Format status for display
                        $statusMap = [
                            Orden::ESTADO_EN_SITIO => 'En Sitio',
                            Orden::ESTADO_EN_PROCESO => 'En Proceso',
                            Orden::ESTADO_EJECUTADA => 'Ejecutada',
                        ];
                        $statusLabel = $statusMap[$statusRaw] ?? ucfirst($statusRaw);

                        // Add order number for context? Maybe too much info.
                        // "Barrio (Status)"
                        return "{$barrio} ({$statusLabel})";
                    })
                    ->badge()
                    ->color(fn($state) => str_contains($state, 'Sin actividad') ? 'gray' : (str_contains($state, 'Ejecutada') ? 'success' : 'primary')),
            ])
            ->paginated(false); // List all technicians usually small number
    }
}
