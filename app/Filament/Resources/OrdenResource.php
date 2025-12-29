<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenResource\Pages;
use App\Models\Orden;
use App\Models\User;
use App\Models\OrdenFoto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Get;
use App\Models\Vehicle;
use Filament\Tables\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\View as FormView;

class OrdenResource extends Resource
{
    protected static ?string $model = Orden::class;

    protected static ?string $navigationLabel = 'Órdenes de Servicio';
    protected static ?string $modelLabel = 'Orden de Servicio';

    protected static ?string $navigationGroup = 'Gestión de Órdenes';
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    Section::make('Información Principal')
                        ->schema([
                            TextInput::make('numero_orden')
                                ->label('Número de Orden')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->default(fn () => (Orden::max('numero_orden') ?? 0) + 1),
                            
                            TextInput::make('servicio')->label('Servicio'),                            
                            TextInput::make('nombre_cliente')->label('Nombre del Cliente')->required(),
                            DateTimePicker::make('fecha_hora')->label('Fecha y Hora')->required(),
                        ])->columnSpan(1),
                    
                    Section::make('Detalles del Servicio')
                        ->schema([
                            TextInput::make('numero_expediente')->label('Número de Expediente'),
                            TextInput::make('movimiento')->label('Movimiento'),

                            TextInput::make('valor_servicio')->label('Valor del Servicio')->numeric()->prefix('$'),
                            Select::make('technician_id')
                                ->label('Técnico Asignado')
                                ->relationship(
                                    'technician', 
                                    'name', 
                                    modifyQueryUsing: fn (Builder $query) => $query
                                        ->whereHas('roles', fn ($q) => $q->where('name', 'tecnico'))
                                        ->where('is_active', true)
                                )
                                ->searchable()
                                ->preload(),
                            Select::make('tipo_activo')
                                ->label('Tipo de activo (Placa)')
                                ->options(Vehicle::pluck('placa', 'placa')) // Carga las placas de los vehículos
                                ->searchable() // Permite buscar en la lista
                                ->required(),
                        ])->columnSpan(1),
                ]),

                Section::make('Referencia del Servicio')
                    ->schema([
                        TextInput::make('placa')->label('Placa'),
                        TextInput::make('marca')->label('Marca'),
                        TextInput::make('referencia')->label('Referencia')
                    ]),

                Section::make('Información de Contacto en Origen')
                    ->schema([
                        TextInput::make('nombre_asignado')->label('Nombre del Asegurado'),
                        TextInput::make('celular')->label('Celular')->tel(),
                    ]),
                
                Grid::make(2)->schema([
                    Section::make('Origen')
                        ->schema([
                            TextInput::make('ciudad_origen')->label('Ciudad de Origen')->required(),
                            TextInput::make('direccion_origen')->label('Dirección de Origen')->required(),
                            Textarea::make('observaciones_origen')->label('Observaciones de Origen')->rows(3),
                        ])->columnSpan(1),

                    Section::make('Destino')
                        ->schema([
                            TextInput::make('ciudad_destino')->label('Ciudad de Destino')->required(),
                            TextInput::make('direccion_destino')->label('Dirección de Destino')->required(),
                            Textarea::make('observaciones_destino')->label('Observaciones de Destino')->rows(3),
                        ])->columnSpan(1),
                ]),

                Section::make('Información Adicional')
                    ->schema([
                        Textarea::make('observaciones_generales')->label('Observaciones Generales')->rows(4),
                        Select::make('status')
                            ->label('Estado de la Orden')
                            ->options([
                                'abierta' => 'Abierta',
                                'programada' => 'Programada',
                                'en proceso' => 'En Proceso',
                                'cerrada' => 'Cerrada',
                                'fallida' => 'Fallida',
                                'anulada' => 'Anulada',
                            ])
                            ->required()
                            ->default('abierta')
                            ->live(),

                        DateTimePicker::make('fecha_programada')
                            ->label('Fecha Programada')
                            ->visible(fn (Get $get): bool => $get('status') === 'programada')
                            ->requiredIf('status', 'programada'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_orden')->label('Numero de orden')->searchable(),
                TextColumn::make('nombre_cliente')->label('Nombre del cliente')->searchable(),
                TextColumn::make('numero_expediente')->label('Numero de expediente')->searchable(),
                TextColumn::make('placa')->label('Placa')->searchable(),
                TextColumn::make('valor_servicio')->label('Valor del servicio')->money('COP')->sortable(),
                TextColumn::make('technician.name')->label('Tecnico')->searchable(),
                TextColumn::make('servicio')->label('Tipo de servicio')->searchable(),
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'primary' => 'abierta',
                        'warning' => 'en proceso',
                        'success' => 'cerrada',
                        'danger' => 'fallida',
                        'gray' => 'anulada',
                    ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Action::make('downloadPdf')
                        ->label('PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Orden $record) {
                            $pdf = Pdf::loadView('pdf.orden-pdf', ['orden' => $record]);
                            return response()->streamDownload(fn() => print($pdf->output()), 'orden-'.$record->numero_orden.'.pdf');
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdens::route('/'),
            'create' => Pages\CreateOrden::route('/create'),
            'edit' => Pages\EditOrden::route('/{record}/edit'),
        ];
    }
}
