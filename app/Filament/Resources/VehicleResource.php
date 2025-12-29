<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Vehículos';
    protected static ?string $modelLabel = 'Vehículo';
    protected static ?string $navigationGroup = 'Administración';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Básica')
                    ->schema([
                        TextInput::make('placa')->label('Placa')->required()->unique(ignoreRecord: true)->maxLength(10),
                        TextInput::make('marca')->label('Marca')->maxLength(255),
                        TextInput::make('modelo')->label('Modelo')->maxLength(255),
                    ])->columns(3),
                
                Section::make('Documentación del Vehículo')
                    ->schema([
                        TextInput::make('tarjeta_propiedad')->label('Número de Tarjeta de Propiedad'),
                        DatePicker::make('fecha_tecnomecanica')->label('Fecha Vencimiento Tecnomecánica'),
                        DatePicker::make('fecha_soat')->label('Fecha Vencimiento SOAT'),
                    ])->columns(3),

                Section::make('Mantenimiento')
                    ->schema([
                        TextInput::make('mantenimiento_preventivo_taller')->label('Taller de Mantenimiento Preventivo'),
                        DatePicker::make('fecha_mantenimiento')->label('Fecha Próximo Mantenimiento'),
                        DatePicker::make('fecha_ultimo_aceite')->label('Fecha Último Cambio de Aceite'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('placa')->label('Placa')->searchable()->sortable(),
                TextColumn::make('marca')->label('Marca')->searchable(),
                TextColumn::make('modelo')->label('Modelo')->searchable(),
                TextColumn::make('user.name')->label('Asignado a')->placeholder('Sin asignar'),
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
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }    
}