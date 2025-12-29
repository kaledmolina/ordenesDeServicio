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
                // SECCIÓN 1: ENCABEZADO DEL CLIENTE
                Section::make('Encabezado del Cliente')
                    ->schema([
                        Select::make('cliente_id')
                            ->label('Código - Nombres Cliente')
                            ->relationship('cliente', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $user = User::find($state);
                                if ($user) {
                                    $set('direccion', $user->direccion);
                                    $set('cedula', $user->email); // Usando email como ejemplo de dato único, cambiar a cedula si existe
                                    $set('telefono', $user->telefono);
                                }
                            })
                            ->columnSpan(2),
                        TextInput::make('direccion')->label('DIRECCION')->columnSpan(1),
                        TextInput::make('cedula')->label('CEDULA')->columnSpan(1),
                        TextInput::make('precinto')->label('PRECINTO')->columnSpan(1),
                    ])->columns(5),

                // SECCIÓN 2: DATOS DE LA ORDEN
                Section::make('Datos de la Orden')
                    ->schema([
                        Select::make('tipo_orden')
                            ->label('TIPO ORDEN')
                            ->options([
                                '003' => '003 PUNTO ADICIONAL',
                                '005' => '005 CORTE',
                                '008' => '008 TRASLADOS',
                                '009' => '009 RECONEXIONES',
                                '015' => '015 MULTA',
                                '016' => '016 RETIRO DEFINITIVO',
                                '017' => '017 EMISION DE PUBLICIDAD',
                                '021' => '021 MATERIALES',
                                '022' => '022 SOPORTE REMOTO',
                                '023' => '023 DESCUENTO INTERNET',
                                '024' => '024 INTERNET',
                                '025' => '025 REVISION TECNICA',
                                '026' => '026 CORTE INTERNET',
                                '027' => '027 SUSPENSION INTERNET',
                                '028' => '028 CAMBIO ESTADO',
                                '029' => '029 TRASLADO INTERNO INT',
                                '030' => '030 CUENTA COBRO',
                                '031' => '031 COPIAS Y REPRODUCCIONES',
                                '032' => '032 TRASLADO INTERNET',
                                '033' => '033 RECONEXION INTERNET',
                                '034' => '034 RETIRO INTERNET',
                                '035' => '035 ORDEN DE APOYO',
                                '036' => '036 INSTALACION CAMARA',
                                '037' => '037 CAMBIO CONTRASEÑA',
                                '044' => '044 CAMBIO PLAN INTERNET',
                                '048' => '048 CAMBIO PLAN TV',
                            ])
                            ->searchable(),
                        Select::make('tipo_funcion')
                            ->label('TIPO FUNCION')
                            ->options([
                                '1 Suscriptor' => '1 Suscriptor',
                                '2 Red' => '2 Red',
                            ]),
                        Forms\Components\DatePicker::make('fecha_trn')->label('FECHA TRN'),
                        Forms\Components\DatePicker::make('fecha_vencimiento')->label('F. VENC'),
                        TextInput::make('numero_orden')
                            ->label('NUMERO')
                            ->default(fn () => (Orden::max('numero_orden') ?? 0) + 1)
                            ->readOnly(),
                        TextInput::make('estado_orden')->label('ESTADO ORDEN')->default('AP'), // AP = Aprobada/Aperturada?
                        TextInput::make('tipo')->label('TIPO')->default('REI'),
                        TextInput::make('estado_interno')->label('ESTADO')->default('A'),
                    ])->columns(4),

                // SECCIÓN 3: DATOS DE CONTACTO Y ESTADO
                Section::make('Datos de Contacto y Estado')
                    ->schema([
                        TextInput::make('direccion_asociado')->label('DIRECCION ASOCIADO'),
                        TextInput::make('telefono')->label('TELEFONO'),
                        TextInput::make('saldo_cliente')->label('SALDO CLIENTE')->numeric()->prefix('$'),
                        TextInput::make('solicitado_por')->label('SOLICITADO POR'),
                        TextInput::make('estado_tv')->label('ESTADO T.V'),
                    ])->columns(3),

                // SECCIÓN 4: ASIGNACIÓN TÉCNICA Y DIAGNÓSTICO
                Section::make('Asignación Técnica y Diagnóstico')
                    ->schema([
                        Select::make('technician_id') // Mapped to tecnico_principal
                            ->label('Empleado / Técnico')
                            ->relationship('technician', 'name', fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'tecnico')))
                            ->searchable()
                            ->preload(),
                        Select::make('tecnico_auxiliar_id')
                            ->label('Técnico Auxiliar')
                            ->relationship('tecnicoAuxiliar', 'name', fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'tecnico')))
                            ->searchable()
                            ->preload(),
                        Select::make('solicitud_suscriptor')
                            ->label('SOLICITUD SUSCRIPTOR (Reporte)')
                            ->options([
                                '1' => '1 SERVICIO INTERMITENTE',
                                '2' => '2 SIN SERVICIO DE INTERNET',
                                '3' => '3 SIN ALCANCE POTENCIA',
                                '4' => '4 SERVICIO LENTO',
                                '5' => '5 SIN SERVICIO DE TELEVISION',
                                '132' => '132 CAMBIO DE CUENTA EN WIM',
                                '133' => '133 CAMBIO DE EQUIPO',
                                '136' => '136 LUZ ROJA',
                                '137' => '137 MANTENIMIENTO CORRECTIVO',
                                '139' => '139 INSTALACION AUTOMONITOR',
                                '140' => '140 REVISION TCA AUTOMONITOR',
                                '142' => '142 GARANTIA INTERNET',
                                '143' => '143 GARANTIA TV',
                                '144' => '144 GARANTIA TV E INTERNET',
                            ])
                            ->searchable(),
                        Select::make('solucion_tecnico')
                            ->label('SOLUCIÓN TÉCNICO')
                            ->options([
                                '1 CAMBIO - CONECTOR' => '1 CAMBIO - CONECTOR',
                                '2 REINICIO EQUIPOS' => '2 REINICIO EQUIPOS',
                                '3 CAMBIO EQUIPO' => '3 CAMBIO EQUIPO',
                            ])
                            ->searchable(),
                    ])->columns(2),

                // SECCIÓN 5: TOTALES Y OBSERVACIONES
                Section::make('Totales y Observaciones')
                    ->schema([
                        TextInput::make('valor_total')->label('VALOR TOTAL')->numeric()->prefix('$'),
                        Textarea::make('observaciones')->label('OBSERVACIONES')->columnSpanFull(),
                    ])->columns(2),

                // SECCIÓN 6: DETALLE DE ARTÍCULOS (REPEATER)
                Section::make('Detalle de Artículos')
                    ->schema([
                        Repeater::make('articulos')
                            ->schema([
                                TextInput::make('grupo_articulo')->label('Grupo Articulo'),
                                TextInput::make('articulo')->label('Articulo'), // Idealmente un Select
                                TextInput::make('valor_unitario')
                                    ->label('V. Unitario')
                                    ->numeric()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                        $cant = $get('cantidad') ?? 0;
                                        $set('total', $state * $cant);
                                    }),
                                TextInput::make('cantidad')
                                    ->label('Cant.')
                                    ->numeric()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                        $val = $get('valor_unitario') ?? 0;
                                        $set('total', $state * $val);
                                    }),
                                TextInput::make('porcentaje_iva')->label('% IVA')->numeric()->default(19),
                                TextInput::make('valor_iva')->label('V. IVA')->numeric()->readOnly(),
                                TextInput::make('total')->label('Total')->numeric()->readOnly(),
                            ])
                            ->columns(7)
                            ->defaultItems(1)
                            ->live(), 
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
                        ->label('Descargar PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (Orden $record) {
                            $pdf = Pdf::loadView('pdf.orden-pdf', ['orden' => $record, 'is_blank' => false]);
                            return response()->streamDownload(fn() => print($pdf->output()), 'orden-'.$record->numero_orden.'.pdf');
                        }),
                    Action::make('downloadBlankPdf')
                        ->label('Descargar Formato Vacio')
                        ->icon('heroicon-o-document')
                        ->action(function (Orden $record) {
                            $pdf = Pdf::loadView('pdf.orden-pdf', ['orden' => $record, 'is_blank' => true]);
                            return response()->streamDownload(fn() => print($pdf->output()), 'orden-vacia-'.$record->numero_orden.'.pdf');
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
