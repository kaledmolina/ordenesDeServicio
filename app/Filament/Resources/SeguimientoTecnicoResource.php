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

                TextColumn::make('ordenes_hoy')
                    ->label('Asignadas Hoy')
                    ->state(fn (User $record) => $record->ordenes()
                        ->whereDate('fecha_asignacion', today())
                        ->count()),

                TextColumn::make('ejecutadas_hoy')
                    ->label('Ejecutadas Hoy')
                    ->state(fn (User $record) => $record->ordenes()
                        ->where('estado_orden', 'ejecutada')
                        ->whereDate('fecha_fin_atencion', today())
                        ->count())
                    ->color('success')
                    ->weight('bold'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Ver Tiempos')
                    ->icon('heroicon-o-clock'),
            ])
            ->bulkActions([]);
    }
    
    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdenesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeguimientoTecnicos::route('/'),
            'edit' => Pages\EditSeguimientoTecnico::route('/{record}/edit'),
        ];
    }
}
