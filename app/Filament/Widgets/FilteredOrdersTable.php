<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Orden;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\BadgeColumn; // <-- Se asegura que este import esté presente
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

class FilteredOrdersTable extends BaseWidget
{
    // Esta propiedad recibirá el estado desde la URL
    public ?string $status = '';

    public static function canView(): bool
    {
        // Only visible if a status filter IS present
        return !empty(request()->query('status'));
    }

    protected function getTableQuery(): Builder
    {
        $query = Orden::query();

        // 1. Filtrar por rol: si NO es admin/operador, ver solo sus órdenes
        $user = auth()->user();
        if (!$user->hasAnyRole(['administrador', 'operador'])) {
            $query->where('technician_id', $user->id);
        }

        // 2. Filtro (si viene de la URL)
        if (!empty($this->status) && $this->status !== 'todas') {
            $query->where('estado_orden', $this->status);
        }

        return $query;
    }

    protected function getTableHeading(): string
    {
        if (empty($this->status) || $this->status === 'todas') {
            return 'Todas las Órdenes';
        }
        return 'Órdenes en estado: ' . ucfirst($this->status);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('direccion')
                    ->label('Dirección')
                    ->searchable(),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable(),

                TextColumn::make('tipo_orden')
                    ->label('Tipo Orden')
                    ->sortable(),

                TextColumn::make('technician.name')
                    ->label('Técnico')
                    ->searchable()
                    ->placeholder('Sin asignar')
                    ->visible(fn() => auth()->user()->hasAnyRole(['administrador', 'operador'])),

                TextColumn::make('fecha_trn')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),

                BadgeColumn::make('estado_orden')
                    ->label('Estado')
                    ->colors([
                        'gray' => Orden::ESTADO_PENDIENTE,
                        'warning' => Orden::ESTADO_ASIGNADA,
                        'primary' => Orden::ESTADO_EN_SITIO,
                        'info' => Orden::ESTADO_EN_PROCESO,
                        'success' => Orden::ESTADO_EJECUTADA,
                        'danger' => Orden::ESTADO_CERRADA,
                        'gray' => Orden::ESTADO_ANULADA,
                    ])
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('technician')
                    ->label('Técnico')
                    ->relationship('technician', 'name', modifyQueryUsing: fn($query) => $query->whereHas('roles', fn($q) => $q->where('name', 'tecnico'))),

                TernaryFilter::make('sin_tecnico')
                    ->label('Sin Técnico Asignado')
                    ->nullable()
                    ->attribute('technician_id')
                    ->trueLabel('Sí')
                    ->falseLabel('No')
            ]);
    }
}

