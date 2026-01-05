<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderFeedbackResource\Pages;
use App\Models\OrderFeedback;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class OrderFeedbackResource extends Resource
{
    protected static ?string $model = OrderFeedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Calificaciones';
    protected static ?string $modelLabel = 'Calificación';
    protected static ?string $pluralModelLabel = 'Calificaciones';
    protected static ?string $navigationGroup = 'Gestión de Técnicos';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('technician_id')
                    ->relationship('technician', 'name')
                    ->disabled(),
                Forms\Components\TextInput::make('orden_id')
                    ->formatStateUsing(fn ($record) => $record->orden->numero_orden)
                    ->label('Orden')
                    ->disabled(),
                Forms\Components\TextInput::make('rating')
                    ->label('Estrellas')
                    ->disabled(),
                
                Forms\Components\Toggle::make('arrived_on_time')->label('Puntualidad')->disabled(),
                Forms\Components\Toggle::make('is_friendly')->label('Amabilidad')->disabled(),
                Forms\Components\Toggle::make('problem_solved')->label('Solución')->disabled(),
                Forms\Components\Toggle::make('wears_uniform')->label('Presentación')->disabled(),
                Forms\Components\Toggle::make('left_clean')->label('Limpieza')->disabled(),
                
                Forms\Components\Textarea::make('comment')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('technician.name')
                    ->label('Técnico')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('orden.numero_orden')
                    ->label('Orden')
                    ->searchable(),
                
                TextColumn::make('rating')
                    ->label('Calificación')
                    ->formatStateUsing(fn (string $state): string => str_repeat('⭐', $state))
                    ->sortable(),

                IconColumn::make('arrived_on_time')->label('Puntual')->boolean(),
                IconColumn::make('is_friendly')->label('Amable')->boolean(),
                IconColumn::make('problem_solved')->label('Solucionó')->boolean(),
                IconColumn::make('wears_uniform')->label('Uniforme')->boolean(),
                IconColumn::make('left_clean')->label('Limpio')->boolean(),
                
                TextColumn::make('comment')
                    ->label('Comentario')
                    ->limit(30)
                    ->tooltip(fn (TextColumn $column): ?string => $column->getState()),
                
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->options([
                        1 => '1 Estrella',
                        2 => '2 Estrellas',
                        3 => '3 Estrellas',
                        4 => '4 Estrellas',
                        5 => '5 Estrellas',
                    ]),
                SelectFilter::make('technician_id')
                    ->relationship('technician', 'name')
                    ->label('Técnico'),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderFeedbacks::route('/'),
        ];
    }
}
