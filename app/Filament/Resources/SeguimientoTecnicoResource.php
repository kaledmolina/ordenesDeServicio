<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeguimientoTecnicoResource\Pages;
use App\Models\User;
use App\Models\Orden;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SeguimientoTecnicoResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Seguimiento Técnicos';
    protected static ?string $modelLabel = 'Técnico';
    protected static ?string $pluralModelLabel = 'Seguimiento Técnicos';
    protected static ?string $navigationGroup = 'Gestión de Órdenes';
    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole(['administrador', 'operador']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn () => User::query()->whereHas('roles', fn ($q) => $q->where('name', 'tecnico')))
            ->columns([
                TextColumn::make('name')
                    ->label('Técnico')
                    ->searchable()
                    ->sortable(),

                // Conteo de órdenes asignadas hoy
                TextColumn::make('ordenes_hoy')
                    ->label('Asignadas Hoy')
                    ->state(fn (User $record) => $record->ordenes()
                        ->whereDate('fecha_asignacion', today())
                        ->count()),

                // Conteo de órdenes ejecutadas hoy
                TextColumn::make('ejecutadas_hoy')
                    ->label('Ejecutadas Hoy')
                    ->state(fn (User $record) => $record->ordenes()
                        ->where('estado_orden', 'ejecutada')
                        ->whereDate('fecha_fin_atencion', today())
                        ->count())
                    ->color('success')
                    ->weight('bold'),

                // Promedio: Tiempo en Llegar (Asignación -> Llegada)
                TextColumn::make('avg_llegada')
                    ->label('T. Prom. Llegada')
                    ->state(fn (User $record) => self::calculateAverageDuration($record, 'fecha_asignacion', 'fecha_llegada'))
                    ->description('Asignación -> Sitio'),

                // Promedio: Tiempo en Atender (Asignación -> Inicio o Llegada -> Inicio?)
                // User asked: "cuanto tarde en atenderla" -> Usually Arrival -> Start Attention
                TextColumn::make('avg_atencion')
                    ->label('T. Prom. Inicio')
                    ->state(fn (User $record) => self::calculateAverageDuration($record, 'fecha_llegada', 'fecha_inicio_atencion'))
                    ->description('Sitio -> Inicio'),

                // Promedio: Tiempo en Terminar (Inicio -> Fin)
                TextColumn::make('avg_ejecucion')
                    ->label('T. Prom. Ejecución')
                    ->state(fn (User $record) => self::calculateAverageDuration($record, 'fecha_inicio_atencion', 'fecha_fin_atencion'))
                    ->description('Inicio -> Fin'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Action to view detailed orders if needed (could rely on relation manager or filter link)
                Tables\Actions\Action::make('ver_ordenes')
                    ->label('Ver Órdenes')
                    ->icon('heroicon-o-eye')
                    ->url(fn (User $record) => \App\Filament\Resources\OrdenResource::getUrl('index', [
                        'tableFilters' => [
                            'technician' => ['value' => $record->id], // Assuming filter exists or simply redirect
                        ]
                    ]))
            ])
            ->bulkActions([]);
    }
    
    // Helper to calculate average duration in human readable format
    protected static function calculateAverageDuration(User $record, string $startCol, string $endCol): string
    {
        $orders = $record->ordenes()
            ->whereNotNull($startCol)
            ->whereNotNull($endCol)
            ->get();

        if ($orders->isEmpty()) {
            return '-';
        }

        $totalMinutes = 0;
        $count = 0;

        foreach ($orders as $order) {
            $start = $order->$startCol;
            $end = $order->$endCol;
            
            // Ensure they are Carbon instances (should be cast in Model)
            if ($start && $end) {
                $totalMinutes += $start->diffInMinutes($end);
                $count++;
            }
        }

        if ($count === 0) return '-';

        $avgMinutes = $totalMinutes / $count;
        
        $hours = floor($avgMinutes / 60);
        $minutes = round($avgMinutes % 60);

        return "{$hours}h {$minutes}m";
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeguimientoTecnicos::route('/'),
        ];
    }
}
