<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RankingTecnicosMes extends BaseWidget
{
    protected static ?string $heading = '📅 Ranking Mensual (Órdenes Cerradas)';
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
                User::query()
                    ->whereHas('roles', fn($q) => $q->where('name', 'tecnico'))
                    ->withCount([
                        'ordenes as ordenes_cerradas_mes_count' => function (Builder $query) {
                            $query->where('estado_orden', 'cerrada')
                                ->whereMonth('fecha_cierre', now()->month)
                                ->whereYear('fecha_cierre', now()->year);
                        }
                    ])
                    ->orderByDesc('ordenes_cerradas_mes_count')
                    ->take(10) // Top 10
            )
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Técnico')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('ordenes_cerradas_mes_count')
                    ->label('Cerradas Mes')
                    ->badge()
                    ->color('primary')
                    ->alignCenter(),
            ])
            ->paginated(false);
    }
}
