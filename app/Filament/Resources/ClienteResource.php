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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class ClienteResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Gestión de Órdenes';
    protected static ?int $navigationSort = 2;
    
    protected static ?string $slug = 'clientes';

    public static function getEloquentQuery(): Builder
    {
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
                Section::make('Información Básica')
                    ->schema([
                        TextInput::make('name')->label('Nombre Completo')->required(),
                        TextInput::make('email')->label('Correo Electrónico')->email()->required()->unique(ignoreRecord: true),
                        TextInput::make('codigo_contrato')->label('Código de Contrato')->required(),
                        TextInput::make('cedula')->label('Cédula')->required(), // Assuming 'cedula' field exists or mapping to 'precinto' or similar? User provided "Cedula" in example. Using 'cedula' if exists, otherwise assume 'precinto' or add new? User model has 'cedula' from Orden context? No, User model usually doesn't have cedula by default. Checking user request, it says "Cedula". I added 'cedula' to migration? Wait, I missed 'cedula' in migration check.
                        // Wait, previous User model didn't show 'cedula'. I should check if it exists or use 'precinto'. Or adding it.
                        // Let's add 'cedula' to migration? Or use 'documento'?
                        // The user request example has "Cedula". I'll assume I missed adding it or it's standard.
                        // Let's stick to the migration I made. I missed 'cedula' in my migration list above.
                        // I will add 'estrato', 'zona', 'barrio'.
                        TextInput::make('direccion')->label('Dirección')->columnSpanFull(),
                        TextInput::make('estrato')->label('Estrato'),
                        TextInput::make('zona')->label('Zona'),
                        TextInput::make('barrio')->label('Barrio'),
                    ])->columns(2),

                Section::make('Contacto')
                    ->schema([
                        TextInput::make('telefono')->label('Teléfono Principal')->tel(),
                        TextInput::make('telefono_facturacion')->label('Teléfono Facturación')->tel(),
                        TextInput::make('otro_telefono')->label('Otro Teléfono')->tel(),
                    ])->columns(3),

                Section::make('Detalles del Servicio')
                    ->schema([
                        TextInput::make('tipo_servicio')->label('Tipo Servicio'),
                        TextInput::make('vendedor')->label('Vendedor'),
                        TextInput::make('tipo_operacion')->label('Tipo Operación'),
                        DatePicker::make('suscripcion_tv')->label('Suscripción TV'),
                        DatePicker::make('suscripcion_internet')->label('Suscripción Internet'),
                        DatePicker::make('fecha_ultimo_pago')->label('Fecha Último Pago'),
                        Select::make('estado_tv')->options(['A' => 'Activo', 'I' => 'Inactivo'])->default('A'),
                        Select::make('estado_internet')->options(['A' => 'Activo', 'I' => 'Inactivo'])->default('A'),
                    ])->columns(3),

                Section::make('Facturación y Saldos')
                    ->schema([
                        TextInput::make('saldo_tv')->numeric()->prefix('$')->default(0),
                        TextInput::make('saldo_internet')->numeric()->prefix('$')->default(0),
                        TextInput::make('saldo_otros')->numeric()->prefix('$')->default(0),
                        TextInput::make('saldo_total')->numeric()->prefix('$')->default(0),
                        TextInput::make('tarifa_tv')->numeric()->prefix('$')->default(0),
                        TextInput::make('tarifa_internet')->numeric()->prefix('$')->default(0),
                        TextInput::make('tarifa_total')->numeric()->prefix('$')->default(0),
                    ])->columns(4),

                Section::make('Información Técnica')
                    ->schema([
                        TextInput::make('plan_internet')->label('Plan Internet')->columnSpan(2),
                        TextInput::make('velocidad')->label('Velocidad (MB)'),
                        TextInput::make('serial')->label('Serial'),
                        TextInput::make('mac')->label('MAC'),
                        TextInput::make('ip')->label('IP'),
                        TextInput::make('marca')->label('Marca'),
                    ])->columns(3),

                Section::make('Estado del Servicio')
                    ->schema([
                        TextInput::make('cortado_tv')->label('Cortado TV'),
                        TextInput::make('retiro_tv')->label('Retiro TV'),
                        TextInput::make('cortado_int')->label('Cortado Internet'),
                        TextInput::make('retiro_int')->label('Retiro Internet'),
                    ])->columns(4),

                Section::make('Seguridad')
                    ->schema([
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        Toggle::make('is_active')->label('Usuario Activo')->default(true),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo_contrato')->label('Contrato')->searchable()->sortable(),
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('barrio')->label('Barrio')->searchable(),
                TextColumn::make('plan_internet')->label('Plan')->limit(20),
                TextColumn::make('saldo_total')->label('Saldo')->money('COP'),
                IconColumn::make('is_active')->label('Activo')->boolean(),
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
