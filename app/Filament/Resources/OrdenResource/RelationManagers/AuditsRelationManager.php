<?php

namespace App\Filament\Resources\OrdenResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AuditsRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $recordTitleAttribute = 'description';
    protected static ?string $title = 'Historial de Cambios';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\KeyValue::make('properties.attributes')
                    ->label('Nuevos Valores')
                    ->keyLabel('Campo')
                    ->valueLabel('Valor')
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('properties.old')
                    ->label('Valores Anteriores')
                    ->keyLabel('Campo')
                    ->valueLabel('Valor')
                    ->visible(fn ($record) => isset($record->properties['old']))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Usuario')
                    ->default('Sistema')
                    ->searchable(),
                Tables\Columns\TextColumn::make('event')
                    ->label('Acción')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'created' => 'Creado',
                        'updated' => 'Actualizado',
                        'deleted' => 'Eliminado',
                        default => $state,
                    })
                    ->badge()
                    ->colors([
                        'success' => 'created',
                        'warning' => 'updated',
                        'danger' => 'deleted',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // No create/attach allowed typically for logs
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver Detalles')
                    ->form(fn (Form $form) => $this->form($form)),
            ])
            ->bulkActions([
                // No bulk actions typically
            ])
            ->defaultSort('created_at', 'desc');
    }
}
