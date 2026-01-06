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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\View as FormView;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class OrdenResource extends Resource
{
    protected static ?string $model = Orden::class;

    protected static ?string $navigationLabel = 'Órdenes de Servicio';
    protected static ?string $modelLabel = 'Orden de Servicio';

    protected static ?string $navigationGroup = 'Gestión de Órdenes';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Si el usuario NO es administrador ni operador, solo ve sus propias órdenes
        if (!Auth::user()->hasAnyRole(['administrador', 'operador'])) {
            $query->where('technician_id', Auth::id());
        }

        return $query;
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // SECCIÓN 1: ENCABEZADO DEL CLIENTE
                Section::make('Encabezado del Cliente')
                    ->disabled(fn () => !Auth::user()->hasAnyRole(['administrador', 'operador']))
                    ->schema([
                        Select::make('cliente_id')
                            ->label('Código - Nombres Cliente')
                            ->relationship('cliente', 'name', fn (Builder $query) => $query->role('cliente'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn () => !Auth::user()->hasAnyRole(['administrador', 'operador']))
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $user = User::find($state);
                                if ($user) {
                                    $set('direccion', $user->direccion);
                                    $set('cedula', $user->cedula); // Corregido: Usar cédula real
                                    $set('telefono', $user->telefono);
                                    $set('nombre_cliente', $user->name);
                                }
                            })
                            ->required()
                            ->unique(
                                'ordens', 
                                'cliente_id', 
                                ignoreRecord: true,
                                modifyRuleUsing: function ($rule) {
                                    return $rule->whereNotIn('estado_orden', ['cerrada', 'anulada']);
                                } 
                            )
                            ->validationMessages([
                                'unique' => 'Este cliente ya tiene una orden activa. Debe cerrar la anterior para crear una nueva.',
                            ])
                            ->columnSpan(2),
                        Hidden::make('nombre_cliente'),
                        TextInput::make('direccion')->label('DIRECCION')->columnSpan(1),
                        TextInput::make('cedula')->label('CEDULA')->columnSpan(1),
                        TextInput::make('precinto')->label('PRECINTO')->columnSpan(1)->disabled(fn () => !Auth::user()->hasAnyRole(['administrador', 'operador'])),
                    ])->columns(5),

                // SECCIÓN 2: DATOS DE LA ORDEN
                Section::make('Datos de la Orden')
                    ->disabled(fn () => !Auth::user()->hasAnyRole(['administrador', 'operador']))
                    ->schema([
                        Select::make('tipo_orden')
                            ->label('TIPO ORDEN')
                            ->options([
                                '025' => '025 REVISION TECNICA',
                                '037' => '037 CAMBIO CONTRASEÑA',
                            ])
                            ->live()
                            ->searchable(),
                        Select::make('tipo_funcion')
                            ->label('TIPO FUNCION')
                            ->options([
                                '1 Suscriptor' => '1 Suscriptor',
                                '2 Red' => '2 Red',
                            ])
                            ->default('1 Suscriptor')
                            ->required(),
                        Forms\Components\DatePicker::make('fecha_trn')->label('FECHA TRN')->required(),
                        Forms\Components\DatePicker::make('fecha_vencimiento')->label('F. VENC'),
                        TextInput::make('numero_orden')
                            ->label('NUMERO')
                            ->default(fn () => (Orden::max('numero_orden') ?? 0) + 1)
                            ->readOnly(),
                        Select::make('estado_orden')
                            ->label('ESTADO ORDEN')
                            ->options([
                                Orden::ESTADO_PENDIENTE => 'Pendiente',
                                Orden::ESTADO_ASIGNADA => 'Asignada',
                                Orden::ESTADO_EN_SITIO => 'En Sitio',
                                Orden::ESTADO_EN_PROCESO => 'En Proceso',
                                Orden::ESTADO_EJECUTADA => 'Ejecutada',
                                Orden::ESTADO_CERRADA => 'Cerrada',
                                Orden::ESTADO_ANULADA => 'Anulada',
                            ])
                            ->default(Orden::ESTADO_PENDIENTE)
                            ->disabled(fn () => !Auth::user()->hasAnyRole(['administrador', 'operador']))
                            ->dehydrated(),
                    ])->columns(4),

                // SECCIÓN 3: DATOS DE CONTACTO Y ESTADO
                Section::make('Datos de Contacto y Estado')
                    ->disabled(fn () => !Auth::user()->hasAnyRole(['administrador', 'operador']))
                    ->schema([
                        TextInput::make('direccion_asociado')->label('DIRECCION ASOCIADO'),
                        TextInput::make('telefono')->label('TELEFONO'),
                        TextInput::make('saldo_cliente')->label('SALDO CLIENTE')->numeric()->prefix('$'),
                        TextInput::make('solicitado_por')->label('SOLICITADO POR'),
                        TextInput::make('estado_tv')->label('ESTADO T.V'),
                    ])->columns(3),

                // SECCIÓN 4: ASIGNACIÓN TÉCNICA Y DIAGNÓSTICO
                Section::make('Asignación Técnica y Diagnóstico')
                    ->disabled(fn () => !Auth::user()->hasAnyRole(['administrador', 'operador']))
                    ->schema([
                        Select::make('technician_id') // Mapped to tecnico_principal
                            ->label('Empleado / Técnico')
                            ->relationship('technician', 'name', fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'tecnico')))
                            ->searchable()
                            ->required()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $set('estado_orden', Orden::ESTADO_ASIGNADA);
                                    $set('fecha_asignacion', now());
                                }
                            }),
                        Select::make('tecnico_auxiliar_id')
                            ->label('Técnico Auxiliar')
                            ->relationship('tecnicoAuxiliar', 'name', fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'tecnico')))
                            ->searchable()
                            ->disabled(fn () => !Auth::user()->hasAnyRole(['administrador', 'operador']))
                            ->preload(),
                        Select::make('solicitud_suscriptor')
                            ->label('SOLICITUD SUSCRIPTOR (Reporte)')
                            ->visible(fn (\Filament\Forms\Get $get) => $get('tipo_orden') === '025')
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
                                '139' => '139 INSTALACION AUTOMONITOREO',
                                '140' => '140 REVISION TCA AUTOMONITOREO',
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
                    ->disabled(fn () => !Auth::user()->hasAnyRole(['administrador', 'operador']))
                    ->schema([
                        TextInput::make('valor_total')->label('VALOR TOTAL')->numeric()->prefix('$'),
                        Textarea::make('observaciones')->label('OBSERVACIONES')->columnSpanFull(),
                    ])->columns(2),

                // SECCIÓN 6: DETALLE DE ARTÍCULOS (REPEATER)
                Section::make('Detalle de Artículos')
                    ->schema([
                        Repeater::make('articulos')
                            ->schema([
                                TextInput::make('grupo_articulo')->label('Articulo')->columnSpan(2),
                                Textarea::make('descripcion')->label('Descripcion')->rows(1)->columnSpan(2),
                                TextInput::make('asoc')->label('ASOC')->columnSpan(1),
                                TextInput::make('valor_unitario')
                                    ->label('V. Unitario')
                                    ->numeric()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                        $cant = $get('cantidad') ?? 0;
                                        $set('total', $state * $cant);
                                    })
                                    ->columnSpan(1),
                                TextInput::make('cantidad')
                                    ->label('Cant.')
                                    ->numeric()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                        $val = $get('valor_unitario') ?? 0;
                                        $set('total', $state * $val);
                                    })
                                    ->columnSpan(1),
                                TextInput::make('total')->label('Total')->numeric()->readOnly()->columnSpan(1),
                            ])
                            ->columns(8)
                            ->defaultItems(1)
                            ->default([
                                ['grupo_articulo' => 'Esclavo con wifi (unidad)', 'descripcion' => '', 'asoc' => '', 'cantidad' => 0, 'valor_unitario' => 0, 'total' => 0],
                                ['grupo_articulo' => 'Mecanico sc/apc', 'descripcion' => '', 'asoc' => '', 'cantidad' => 0, 'valor_unitario' => 0, 'total' => 0],
                                ['grupo_articulo' => 'Cable drop 1 hilo', 'descripcion' => '', 'asoc' => '', 'cantidad' => 0, 'valor_unitario' => 0, 'total' => 0],
                                ['grupo_articulo' => 'Grapas de muro', 'descripcion' => '', 'asoc' => '', 'cantidad' => 0, 'valor_unitario' => 0, 'total' => 0],
                                ['grupo_articulo' => 'Ont', 'descripcion' => '', 'asoc' => '', 'cantidad' => 0, 'valor_unitario' => 0, 'total' => 0],
                                ['grupo_articulo' => 'Canaleta plastica', 'descripcion' => '', 'asoc' => '', 'cantidad' => 0, 'valor_unitario' => 0, 'total' => 0],
                                ['grupo_articulo' => 'Abrazadera metalicas', 'descripcion' => '', 'asoc' => '', 'cantidad' => 0, 'valor_unitario' => 0, 'total' => 0],
                                ['grupo_articulo' => 'Chazos(unidad)', 'descripcion' => '', 'asoc' => '', 'cantidad' => 0, 'valor_unitario' => 0, 'total' => 0],
                                ['grupo_articulo' => 'Tornillos(unidad)', 'descripcion' => '', 'asoc' => '', 'cantidad' => 0, 'valor_unitario' => 0, 'total' => 0],
                                ['grupo_articulo' => 'Amarres plasticos (unidad)', 'descripcion' => '', 'asoc' => '', 'cantidad' => 0, 'valor_unitario' => 0, 'total' => 0],
                                ['grupo_articulo' => 'Cinta bandi(centimetro)', 'descripcion' => '', 'asoc' => '', 'cantidad' => 0, 'valor_unitario' => 0, 'total' => 0],
                                ['grupo_articulo' => 'Clavos', 'descripcion' => '', 'asoc' => '', 'cantidad' => 0, 'valor_unitario' => 0, 'total' => 0],
                                ['grupo_articulo' => 'Conector RG6', 'descripcion' => '', 'asoc' => '', 'cantidad' => 0, 'valor_unitario' => 0, 'total' => 0],
                                ['grupo_articulo' => 'Cable coaxial', 'descripcion' => '', 'asoc' => '', 'cantidad' => 0, 'valor_unitario' => 0, 'total' => 0],
                            ])
                            ->live(), 
                    ]),

                // SECCIÓN 7: EQUIPOS INSTALADOS/RETIRADOS
                Section::make('Equipos Instalados/Retirados')
                    ->schema([
                        TextInput::make('mac_router')->label('Mac Router'),
                        TextInput::make('mac_bridge')->label('Mac Bridge'),
                        TextInput::make('mac_ont')->label('Mac Ont'),
                        TextInput::make('otros_equipos')->label('Otros Equipos'),
                    ])->columns(2),

                // SECCIÓN 8: FIRMAS
                Section::make('Firmas')
                    ->schema([
                        SignaturePad::make('firma_tecnico')
                            ->label('Firma Técnico')
                            ->penColor('#000000') // Tinta negra
                            ->confirmable()
                            ->columnSpan(1),
                        SignaturePad::make('firma_suscriptor')
                            ->label('Firma Suscriptor')
                            ->penColor('#000000') // Tinta negra
                            ->confirmable()
                            ->columnSpan(1),
                    ])->columns(2),
                
                // SECCIÓN 9: EVIDENCIA FOTOGRÁFICA
                Section::make('Evidencia Fotográfica')
                    ->schema([
                        Repeater::make('fotos')
                            ->relationship()
                            ->schema([
                                FileUpload::make('path')
                                    ->label('Foto')
                                    ->image()
                                    ->disk('public') // Cambiado a public para que se vea en el panel
                                    ->directory('orden-fotos')
                                    ->columnSpanFull(),
                            ])
                            ->grid(2)
                            ->defaultItems(0)
                            ->addActionLabel('Agregar Foto')
                            ->reorderableWithButtons(),
                    ])
                    ->collapsible(),

                // Hidden fields for tracking
                Hidden::make('fecha_asignacion'),
                Hidden::make('fecha_inicio_atencion'),
                Hidden::make('fecha_fin_atencion'),
                Hidden::make('fecha_cierre'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_orden')
                    ->label('N° Orden')
                    ->searchable()
                    ->url(fn (Orden $record) => route('orden.pdf.stream', $record))
                    ->openUrlInNewTab(),
                TextColumn::make('technician.name')
                    ->label('Técnico')
                    ->searchable()
                    ->url(fn (Orden $record) => route('orden.pdf.stream', $record))
                    ->openUrlInNewTab(),
                TextColumn::make('fecha_trn')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable()
                    ->url(fn (Orden $record) => route('orden.pdf.stream', $record))
                    ->openUrlInNewTab(),
                BadgeColumn::make('estado_orden')
                    ->label('Estado')
                    ->url(fn (Orden $record) => route('orden.pdf.stream', $record))
                    ->openUrlInNewTab()
                    ->colors([
                        'gray' => Orden::ESTADO_PENDIENTE,
                        'warning' => Orden::ESTADO_ASIGNADA,
                        'primary' => Orden::ESTADO_EN_SITIO,
                        'info' => Orden::ESTADO_EN_PROCESO,
                        'success' => Orden::ESTADO_EJECUTADA,
                        'danger' => Orden::ESTADO_CERRADA,
                        'gray' => Orden::ESTADO_ANULADA,
                    ]),
            ])
            ->filters([
                SelectFilter::make('estado_orden')
                    ->label('Estado')
                    ->options([
                        Orden::ESTADO_PENDIENTE => 'Pendiente',
                        Orden::ESTADO_ASIGNADA => 'Asignada',
                        Orden::ESTADO_EN_SITIO => 'En Sitio',
                        Orden::ESTADO_EN_PROCESO => 'En Proceso',
                        Orden::ESTADO_EJECUTADA => 'Ejecutada',
                        Orden::ESTADO_CERRADA => 'Cerrada',
                        Orden::ESTADO_ANULADA => 'Anulada',
                    ]),
                SelectFilter::make('technician_id')
                    ->label('Técnico')
                    ->relationship('technician', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('tipo_orden')
                    ->label('Tipo de Orden')
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
                Filter::make('fecha_trn')
                    ->form([
                        Forms\Components\DatePicker::make('fecha_desde')->label('Fecha Desde'),
                        Forms\Components\DatePicker::make('fecha_hasta')->label('Fecha Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['fecha_desde'],
                                fn (Builder $query, $date) => $query->whereDate('fecha_trn', '>=', $date),
                            )
                            ->when(
                                $data['fecha_hasta'],
                                fn (Builder $query, $date) => $query->whereDate('fecha_trn', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    // Tables\Actions\ViewAction::make(),
                    Action::make('view')
                        ->label('Ver')
                        ->icon('heroicon-o-eye')
                        ->url(fn (Orden $record) => route('orden.pdf.stream', $record))
                        ->openUrlInNewTab()
                        ->extraAttributes(['target' => '_blank']),

                    Tables\Actions\EditAction::make()
                        ->hidden(fn (Orden $record) => $record->estado_orden === Orden::ESTADO_EJECUTADA && !Auth::user()->hasAnyRole(['administrador', 'operador'])),
                    Action::make('downloadPdf')
                        ->label('Descargar PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (Orden $record) {
                            $pdf = Pdf::loadView('pdf.orden-pdf', ['orden' => $record, 'is_blank' => false])
                                ->setOption(['isRemoteEnabled' => true, 'chroot' => public_path()]);
                            return response()->streamDownload(fn() => print($pdf->output()), 'orden-'.$record->numero_orden.'.pdf');
                        }),
                    Tables\Actions\DeleteAction::make(),
                ]),
                Action::make('aceptarOrden')
                    ->label('Aceptar Orden')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->visible(fn (Orden $record) => $record->estado_orden === Orden::ESTADO_ASIGNADA && $record->technician_id == Auth::id())
                    ->action(function (Orden $record) {
                        $user = Auth::user();
                        
                        // Validar si el técnico ya tiene una orden en proceso (Solo si NO es admin/operador)
                        if (! $user->hasAnyRole(['administrador', 'operador'])) {
                            $activeOrder = Orden::where('technician_id', $user->id)
                                ->where('estado_orden', Orden::ESTADO_EN_PROCESO)
                                ->where('id', '!=', $record->id)
                                ->exists();

                            if ($activeOrder) {
                                Notification::make()
                                    ->title('No puedes iniciar otra orden')
                                    ->body('Ya tienes una orden en proceso. Por favor finalízala antes de iniciar una nueva.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                        }

                        $record->update([
                            'estado_orden' => Orden::ESTADO_EN_PROCESO,
                            'fecha_inicio_atencion' => now(),
                        ]);
                    }),
                Action::make('llegarSitio')
                    ->label('Llegué a Sitio')
                    ->icon('heroicon-o-map-pin')
                    ->color('primary')
                    ->visible(fn (Orden $record) => $record->estado_orden === Orden::ESTADO_EN_PROCESO)
                    ->action(function (Orden $record) {
                        $record->update([
                            'estado_orden' => Orden::ESTADO_EN_SITIO,
                            'fecha_llegada' => now(),
                        ]);
                    }),
                Action::make('finalizarAtencion')
                    ->label('Finalizar Atención')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Orden $record) => $record->estado_orden === Orden::ESTADO_EN_SITIO)
                    ->mountUsing(fn (Forms\ComponentContainer $form, Orden $record) => $form->fill([
                        'articulos' => $record->articulos,
                        'mac_router' => $record->mac_router,
                        'mac_bridge' => $record->mac_bridge,
                        'mac_ont' => $record->mac_ont,
                        'otros_equipos' => $record->otros_equipos,
                    ]))
                    ->form([
                        Section::make('Firmas Obligatorias')
                            ->schema([
                                SignaturePad::make('firma_tecnico')
                                    ->label('Firma Técnico')
                                    ->penColor('#000000')
                                    ->confirmable()
                                    ->required() // Obligatorio
                                    ->columnSpan(1),
                                SignaturePad::make('firma_suscriptor')
                                    ->label('Firma Suscriptor')
                                    ->penColor('#000000')
                                    ->confirmable()
                                    ->required() // Obligatorio
                                    ->columnSpan(1),
                            ])->columns(2),

                        Section::make('Detalle de Artículos')
                            ->schema([
                                Repeater::make('articulos')
                                    ->schema([
                                        TextInput::make('grupo_articulo')->label('Articulo')->columnSpan(2),
                                        Textarea::make('descripcion')->label('Descripcion')->rows(1)->columnSpan(2),
                                        TextInput::make('asoc')->label('ASOC')->columnSpan(1),
                                        TextInput::make('valor_unitario')
                                            ->label('V. Unitario')
                                            ->numeric()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                                $cant = $get('cantidad') ?? 0;
                                                $set('total', $state * $cant);
                                            })
                                            ->columnSpan(1),
                                        TextInput::make('cantidad')
                                            ->label('Cant.')
                                            ->numeric()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                                $val = $get('valor_unitario') ?? 0;
                                                $set('total', $state * $val);
                                            })
                                            ->columnSpan(1),
                                        TextInput::make('total')->label('Total')->numeric()->readOnly()->columnSpan(1),
                                    ])
                                    ->columns(8)
                                    ->defaultItems(0) // No items by default here to keep it clean, or 1 if preferred
                                    ->live(), 
                            ])
                            ->collapsed(), // Collapsed by default to keep modal clean

                        Section::make('Equipos Instalados/Retirados')
                            ->schema([
                                TextInput::make('mac_router')->label('Mac Router'),
                                TextInput::make('mac_bridge')->label('Mac Bridge'),
                                TextInput::make('mac_ont')->label('Mac Ont'),
                                TextInput::make('otros_equipos')->label('Otros Equipos'),
                            ])->columns(2)
                            ->collapsed(),

                        Section::make('Evidencia Fotográfica')
                            ->schema([
                                FileUpload::make('evidencias')
                                    ->label('Fotos del Servicio')
                                    ->multiple()
                                    ->image()
                                    ->directory('orden-fotos') // Public disk or private? Controller uses 'local' 'private/orden-fotos'. Let's use 'orden-fotos' on public for Filament usually, or match controller if possible. Stick to default disk for now or 'public'.
                                    ->visibility('public') // Assuming public for Filament view ease
                                    ->columnSpanFull(),
                            ])
                            ->collapsed(),
                    ])
                    ->action(function (Orden $record, array $data) {
                        $record->update([
                            'estado_orden' => Orden::ESTADO_EJECUTADA,
                            'fecha_fin_atencion' => now(),
                            'firma_tecnico' => $data['firma_tecnico'],
                            'firma_suscriptor' => $data['firma_suscriptor'],
                            'articulos' => $data['articulos'] ?? $record->articulos, 
                            'mac_router' => $data['mac_router'],
                            'mac_bridge' => $data['mac_bridge'],
                            'mac_ont' => $data['mac_ont'],
                            'otros_equipos' => $data['otros_equipos'],
                        ]);
                        
                        // Guardar evidencias
                        if (!empty($data['evidencias'])) {
                            foreach ($data['evidencias'] as $path) {
                                $record->fotos()->create([
                                    'path' => $path,
                                    // 'tipo' => 'evidencia' // If specific column exists
                                ]);
                            }
                        }
                    }),
                Action::make('cerrarOrden')
                    ->label('Cerrar Orden')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->visible(fn (Orden $record) => $record->estado_orden === Orden::ESTADO_EJECUTADA && Auth::user()->hasAnyRole(['administrador', 'operador']))
                    ->action(function (Orden $record) {
                        $record->update([
                            'estado_orden' => Orden::ESTADO_CERRADA,
                            'fecha_cierre' => now(),
                        ]);
                    }),
                Action::make('anularOrden')
                    ->label('Anular Orden')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (Orden $record) => !in_array($record->estado_orden, [Orden::ESTADO_ANULADA, Orden::ESTADO_CERRADA]) && Auth::user()->hasAnyRole(['administrador', 'operador']))
                    ->requiresConfirmation()
                    ->action(function (Orden $record) {
                        $record->update([
                            'estado_orden' => Orden::ESTADO_ANULADA,
                        ]);
                    }),
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
