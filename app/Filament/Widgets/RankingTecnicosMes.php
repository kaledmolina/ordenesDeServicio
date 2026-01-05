<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RankingTecnicosMes extends BaseWidget
{
    protected static ?string $heading = '📅 Ranking Mensual (Órdenes Ejecutadas)';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereHas('roles', fn ($q) => $q->where('name', 'tecnico'))
                    ->withCount(['ordenes as ordenes_ejecutadas_mes_count' => function (Builder $query) {
                        $query->where('estado_orden', 'ejecutada')
                              ->whereMonth('fecha_fin_atencion', now()->month)
                              ->whereYear('fecha_fin_atencion', now()->year);
                    }])
                    ->orderByDesc('ordenes_ejecutadas_mes_count')
                    ->take(10) // Top 10
            )
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Técnico')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('ordenes_ejecutadas_mes_count')
                    ->label('Ejecutadas Mes')
                    ->badge()
                    ->color('primary')
                    ->alignCenter(),
            ])
            ->paginated(false);
    }
}
