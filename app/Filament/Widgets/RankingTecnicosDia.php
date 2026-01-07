<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RankingTecnicosDia extends BaseWidget
{
    protected static ?string $heading = '🏆 Ranking Diario (Órdenes Ejecutadas)';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereHas('roles', fn($q) => $q->where('name', 'tecnico'))
                    ->withCount([
                        'ordenes as ordenes_ejecutadas_hoy_count' => function (Builder $query) {
                            $query->where('estado_orden', 'ejecutada')
                                ->whereDate('fecha_fin_atencion', today());
                        }
                    ])
                    ->orderByDesc('ordenes_ejecutadas_hoy_count')
                    ->take(5) // Top 5
            )
            ->columns([
                Tables\Columns\TextColumn::make('index')
                    ->label('#')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Técnico')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('ordenes_ejecutadas_hoy_count')
                    ->label('Ejecutadas Hoy')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
            ])
            ->paginated(false);
    }
}
