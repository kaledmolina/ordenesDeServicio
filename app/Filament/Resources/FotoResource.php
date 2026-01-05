<?php
namespace App\Filament\Resources;

use App\Filament\Resources\FotoResource\Pages;
use App\Models\Orden;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\ViewColumn;

class FotoResource extends Resource
{
    protected static ?string $model = Orden::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Gestión de Fotos';
    protected static ?string $modelLabel = 'Orden';
    protected static ?string $pluralModelLabel = 'Órdenes';

    protected static ?string $navigationGroup = 'Gestión de Órdenes';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole(['administrador', 'operador', 'tecnico']);
    }

    // No se necesita un formulario principal aquí
    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = Orden::query()->withCount('fotos');
                // Solo filtrar por técnico si NO es admin ni operador
                if (!auth()->user()->hasAnyRole(['administrador', 'operador'])) {
                    $query->where('technician_id', auth()->id());
                }
                return $query;
            })
            ->columns([
                TextColumn::make('numero_orden')->label('N° de Orden')->searchable(),
                TextColumn::make('nombre_cliente')->label('Cliente')->searchable(),
                TextColumn::make('fotos_count')->label('N° de Fotos')->sortable(),
                TextColumn::make('estado_orden')->label('Estado')->badge()
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
                ViewColumn::make('fotos_preview')
                    ->view('filament.tables.columns.order-photos')
                    ->label('Fotos'),
            ])
            ->filters([
                Filter::make('sin_fotos')
                    ->label('Órdenes sin Fotos')
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave('fotos'))
            ])
            ->actions([
                // Esta acción redirige a la página dedicada para gestionar las fotos
                Tables\Actions\Action::make('manage_photos')
                    ->label('Gestionar Fotos')
                    ->icon('heroicon-o-camera')
                    ->url(fn (Orden $record): string => self::getUrl('manage-photos', ['record' => $record])),
            ])
            ->bulkActions([]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFotos::route('/'),
            // Se registra la ruta para la nueva página personalizada
            'manage-photos' => Pages\ManageOrderPhotos::route('/{record}/photos'),
        ];
    }    
}