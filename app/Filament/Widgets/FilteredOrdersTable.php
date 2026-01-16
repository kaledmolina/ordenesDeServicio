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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;

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
                TextColumn::make('numero_orden')
                    ->label('N° Orden')
                    ->searchable()
                    ->url(fn(Orden $record) => route('orden.pdf.stream', $record))
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cliente.name')
                    ->label('Cliente')
                    ->searchable()
                    ->url(fn(Orden $record) => route('orden.pdf.stream', $record))
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cliente.codigo_contrato')
                    ->label('Código Cliente')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('cliente.barrio')
                    ->label('Barrio')
                    ->searchable()
                    ->url(fn(Orden $record) => route('orden.pdf.stream', $record))
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('tipo_orden')
                    ->label('Tipo Orden')
                    ->formatStateUsing(fn($state) => Orden::TIPO_ORDEN_OPTIONS[$state] ?? $state)
                    ->searchable()
                    ->url(fn(Orden $record) => route('orden.pdf.stream', $record))
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('clasificacion')
                    ->label('Clasificación')
                    ->badge()
                    ->colors([
                        'success' => 'rapidas',
                        'warning' => 'cuadrilla',
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('solicitud_suscriptor')
                    ->label('Reporte')
                    ->formatStateUsing(fn($state) => Orden::SOLICITUD_SUSCRIPTOR_OPTIONS[$state] ?? $state)
                    ->searchable()
                    ->wrap()
                    ->url(fn(Orden $record) => route('orden.pdf.stream', $record))
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: false),
                BadgeColumn::make('estado_orden')
                    ->label('Estado')
                    ->url(fn(Orden $record) => route('orden.pdf.stream', $record))
                    ->openUrlInNewTab()
                    ->colors([
                        'gray' => [Orden::ESTADO_PENDIENTE, Orden::ESTADO_ANULADA],
                        'warning' => Orden::ESTADO_ASIGNADA,
                        'primary' => Orden::ESTADO_EN_SITIO,
                        'info' => Orden::ESTADO_EN_PROCESO,
                        'success' => Orden::ESTADO_EJECUTADA,
                        'danger' => Orden::ESTADO_CERRADA,
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('technician.name')
                    ->label('Técnico')
                    ->searchable()
                    ->placeholder('Sin asignar')
                    ->visible(fn() => auth()->user()->hasAnyRole(['administrador', 'operador']))
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('fecha_trn')
                    ->label('Fecha')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('estado_orden')
                    ->label('Estado')
                    ->options([
                        Orden::ESTADO_PENDIENTE => 'Pendiente',
                        Orden::ESTADO_ASIGNADA => 'Asignada',
                        Orden::ESTADO_EN_SITIO => 'En Sitio',
                        Orden::ESTADO_EN_PROCESO => 'En Proceso',
                        Orden::ESTADO_EJECUTADA => 'Ejecutada',
                        Orden::ESTADO_CERRADA => 'Cerrada',
                        Orden::ESTADO_ANULADA => 'Anulada',
                    ]),
                SelectFilter::make('clasificacion')
                    ->label('Clasificación')
                    ->options([
                        'rapidas' => 'Rápidas',
                        'cuadrilla' => 'Cuadrilla',
                    ]),
                SelectFilter::make('technician_id')
                    ->label('Técnico')
                    ->relationship('technician', 'name', fn(Builder $query) => $query->whereHas('roles', fn($q) => $q->where('name', 'tecnico')))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('tipo_orden')
                    ->label('Tipo de Orden')
                    ->options(Orden::TIPO_ORDEN_OPTIONS)
                    ->searchable(),
                SelectFilter::make('solicitud_suscriptor')
                    ->label('Reporte')
                    ->options(Orden::SOLICITUD_SUSCRIPTOR_OPTIONS)
                    ->searchable(),
                Filter::make('fecha_trn')
                    ->form([
                        Forms\Components\DatePicker::make('fecha_desde')->label('Fecha Desde'),
                        Forms\Components\DatePicker::make('fecha_hasta')->label('Fecha Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['fecha_desde'],
                                fn(Builder $query, $date) => $query->whereDate('fecha_trn', '>=', $date),
                            )
                            ->when(
                                $data['fecha_hasta'],
                                fn(Builder $query, $date) => $query->whereDate('fecha_trn', '<=', $date),
                            );
                    }),
            ]);
    }
}

