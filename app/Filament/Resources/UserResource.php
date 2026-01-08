<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $modelLabel = 'Usuario';
    protected static ?string $navigationGroup = 'Administración';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole(['administrador', 'operador']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Personal y de Contacto')
                    ->schema([
                        TextInput::make('name')->label('Nombre')->required(),
                        TextInput::make('email')->label('Correo Electrónico')->email()->required()->unique(ignoreRecord: true),
                        TextInput::make('password')->label('Contraseña')->password()->dehydrateStateUsing(fn(string $state): string => Hash::make($state))->dehydrated(fn(?string $state): bool => filled($state))->required(fn(string $operation): bool => $operation === 'create'),
                        TextInput::make('telefono')->label('Teléfono')->tel(),
                        TextInput::make('direccion')->label('Dirección'),

                    ])->columns(2),

                Section::make('Asignación y Rol')
                    ->schema([

                        Select::make('roles')
                            ->label('Rol')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload(),
                        Toggle::make('is_active')
                            ->label('Estado Activo')
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('email')->searchable(),

                TextColumn::make('roles.name')->label('Rol')->badge(),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                TextColumn::make('fcm_tokens_count')
                    ->counts('fcmTokens')
                    ->label('Dispositivos App')
                    ->badge(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([10, 25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
