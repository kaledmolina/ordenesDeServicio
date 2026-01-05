<?php

namespace App\Filament\Resources\SeguimientoTecnicoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrdenesRelationManager extends RelationManager
{
    protected static string $relationship = 'ordenes'; // Relación en User.php
    protected static ?string $title = 'Detalle de Tiempos por Orden';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero_orden')
            ->columns([
                TextColumn::make('numero_orden')
                    ->label('N° Orden')
                    ->searchable(),

                TextColumn::make('estado_orden')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'gray',
                        'asignada' => 'info',
                        'en_sitio' => 'warning',
                        'en_proceso' => 'primary',
                        'ejecutada' => 'success',
                        'cerrada' => 'success',
                        'anulada' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('fecha_asignacion')
                    ->label('Asignada')
                    ->dateTime('d/m H:i')
                    ->sortable(),

                // Tiempos Exactos
                TextColumn::make('tiempo_traslado')
                    ->label('T. Traslado')
                    ->state(fn (Model $record) => self::calcDiff($record->fecha_asignacion, $record->fecha_llegada))
                    ->description('Asignada -> En Sitio'),

                TextColumn::make('tiempo_espera')
                    ->label('T. En Sitio')
                    ->state(fn (Model $record) => self::calcDiff($record->fecha_llegada, $record->fecha_inicio_atencion))
                    ->description('Llegada -> Inicio'),

                TextColumn::make('tiempo_ejecucion')
                    ->label('T. Ejecución')
                    ->state(fn (Model $record) => self::calcDiff($record->fecha_inicio_atencion, $record->fecha_fin_atencion))
                    ->description('Inicio -> Fin'),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }

    protected static function calcDiff($start, $end): string
    {
        if (!$start || !$end) {
            return '-';
        }

        // Asegurar que son Carbon
        $start = \Carbon\Carbon::parse($start);
        $end = \Carbon\Carbon::parse($end);

        $diffInMinutes = $start->diffInMinutes($end);
        
        $hours = floor($diffInMinutes / 60);
        $minutes = $diffInMinutes % 60;

        // Formato: 1h 30m
        return sprintf('%dh %02dm', $hours, $minutes);
    }
}
