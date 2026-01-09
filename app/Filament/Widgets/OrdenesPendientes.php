<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Orden;
use Filament\Notifications\Notification;

class OrdenesPendientes extends BaseWidget
{
    protected static ?string $heading = '📋 Órdenes Pendientes (Solicitar Asignación)';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['administrador', 'operador', 'tecnico']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Orden::query()
                    ->where('estado_orden', 'pendiente')
                    ->whereNull('technician_id')
                    ->latest('created_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('numero_orden')
                    ->label('N°')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cliente.name')
                    ->label('Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cliente.barrio')
                    ->label('Barrio')
                    ->searchable(),
                Tables\Columns\TextColumn::make('clasificacion')
                    ->label('Clasificación')
                    ->badge()
                    ->colors([
                        'success' => 'rapidas',
                        'warning' => 'cuadrilla',
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state ?? '')),
                Tables\Columns\TextColumn::make('tipo_orden')
                    ->label('Tipo')
                    ->formatStateUsing(fn($state) => Orden::TIPO_ORDEN_OPTIONS[$state] ?? $state),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('clasificacion')
                    ->label('Clasificación')
                    ->options([
                        'rapidas' => 'Rápidas',
                        'cuadrilla' => 'Cuadrilla',
                    ]),
                Tables\Filters\SelectFilter::make('tipo_orden')
                    ->label('Tipo')
                    ->options(Orden::TIPO_ORDEN_OPTIONS),
            ])
            ->actions([
                Tables\Actions\Action::make('solicitar')
                    ->label('Solicitar')
                    ->icon('heroicon-o-hand-raised')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Solicitar Orden')
                    ->modalDescription('¿Estás seguro de que deseas tomar esta orden? Se te asignará inmediatamente.')
                    ->action(function (Orden $record) {
                        $user = auth()->user();

                        // Check if technician already has an active order? 
                        // The user request was just "solicitar a que se les asigne".
                        // We will allow assignment.
            
                        $record->update([
                            'technician_id' => $user->id,
                            'estado_orden' => Orden::ESTADO_ASIGNADA,
                            'fecha_asignacion' => now(),
                        ]);

                        Notification::make()
                            ->title('Orden asignada correctamente')
                            ->body('La orden #' . $record->numero_orden . ' ha sido asignada a tu perfil.')
                            ->success()
                            ->send();

                        // Refresh to remove from list
                    }),
            ])
            ->paginated([5, 10, 25]);
    }
}
