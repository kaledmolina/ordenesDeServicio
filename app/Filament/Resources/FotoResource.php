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

class FotoResource extends Resource
{
    protected static ?string $model = Orden::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Gestión de Fotos';
    protected static ?string $modelLabel = 'Orden';
    protected static ?string $pluralModelLabel = 'Órdenes';

    protected static ?string $navigationGroup = 'Gestión de Órdenes';
    protected static ?int $navigationSort = 2;

    // No se necesita un formulario principal aquí
    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Orden::query()->withCount('fotos'))
            ->columns([
                TextColumn::make('numero_orden')->label('N° de Orden')->searchable(),
                TextColumn::make('nombre_cliente')->label('Cliente')->searchable(),
                TextColumn::make('fotos_count')->label('N° de Fotos')->sortable(),
                TextColumn::make('status')->label('Estado')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'abierta' => 'success',
                        'programada' => 'info',
                        'en proceso' => 'warning',
                        'cerrada' => 'primary',
                        default => 'gray',
                    }),
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