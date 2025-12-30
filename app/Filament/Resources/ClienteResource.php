<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class ClienteResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Gestión de Órdenes'; // Or Administration, but user asked for it to be a resource for creating clients
    protected static ?int $navigationSort = 2;
    
    // Ensure we distinguish this resource from the main UserResource by slug
    protected static ?string $slug = 'clientes';

    public static function getEloquentQuery(): Builder
    {
        // Global scope for this resource: only show users with 'cliente' role
        return parent::getEloquentQuery()->role('cliente');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasAnyRole(['administrador', 'operador']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Cliente')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre Completo')
                            ->required(),
                        TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('codigo_contrato')
                            ->label('Código de Contrato')
                            ->required() // Assuming it's required for clients per user request "que tenga un campo"
                            ->columnSpan(1),
                        TextInput::make('telefono')
                            ->label('Teléfono')
                            ->tel(),
                        TextInput::make('direccion')
                            ->label('Dirección')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Seguridad')
                    ->schema([
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo_contrato')->label('Contrato')->searchable()->sortable(),
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('telefono')->label('Teléfono'),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }
}
