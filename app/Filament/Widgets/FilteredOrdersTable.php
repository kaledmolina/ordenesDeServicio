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

    protected function getTableQuery(): Builder
    {
        $query = Orden::query();

        // Si se ha pasado un estado en la URL, se aplica el filtro
        if (!empty($this->status) && $this->status !== 'todas') {
            $query->where('status', $this->status);
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
                TextColumn::make('numero_orden')->label('N° Orden')->searchable(),
                
                TextColumn::make('technician.name')
                    ->label('Técnico Asignado')
                    ->searchable()
                    ->placeholder('Sin asignar'),

                TextColumn::make('celular')->label('Número de Contacto')->placeholder('Sin asignar'),
                TextColumn::make('ciudad_origen')->label('Ciudad Origen')->placeholder('Sin asignar'),
                TextColumn::make('ciudad_destino')->label('Ciudad Destino')->placeholder('Sin asignar'),

                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'abierta',
                        'info'    => 'programada',
                        'warning' => 'en proceso',
                        'primary' => 'cerrada',
                        'danger'  => fn ($state) => in_array($state, ['fallida', 'rechazada']),
                        'gray'    => 'anulada',
                    ])
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('technician')
                    ->label('Técnico')
                    ->relationship('technician', 'name', modifyQueryUsing: fn ($query) => $query->whereHas('roles', fn($q) => $q->where('name', 'tecnico'))),
                
                TernaryFilter::make('sin_tecnico')
                    ->label('Sin Técnico Asignado')
                    ->nullable()
                    ->attribute('technician_id')
                    ->trueLabel('Sí')
                    ->falseLabel('No')
            ]);
    }
}

