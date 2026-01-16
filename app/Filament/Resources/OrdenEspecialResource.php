<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenEspecialResource\Pages;
use App\Models\Orden;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class OrdenEspecialResource extends Resource
{
    protected static ?string $model = Orden::class;
    protected static ?string $slug = 'ordenes-especiales';

    protected static ?string $navigationLabel = 'Órdenes Especiales';
    protected static ?string $modelLabel = 'Orden Especial';

    protected static ?string $navigationGroup = 'Gestión de Órdenes';
    protected static ?int $navigationSort = 2; // Below standard orders

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected function getTableQuery(): Builder
    {
        return Orden::query()
            ->where(function ($query) {
                $query->where('solucion_tecnico', 'like', '%Reprogramar%')
                    ->orWhere('solucion_tecnico', 'like', '%Solicitar Cierre%');
            })
            ->where('estado_orden', '!=', Orden::ESTADO_CERRADA);
    }

    public static function form(Form $form): Form
    {
        // Simple Read-Only Form for context
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Solicitud')
                    ->schema([
                        Forms\Components\TextInput::make('numero_orden')
                            ->label('N° Orden')
                            ->readOnly(),
                        Forms\Components\TextInput::make('nombre_cliente')
                            ->label('Cliente')
                            ->readOnly(),
                        Forms\Components\TextInput::make('solucion_tecnico')
                            ->label('Tipo de Solicitud')
                            ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state)
                            ->readOnly(),
                        Forms\Components\Textarea::make('observaciones')
                            ->label('Motivo / Observaciones')
                            ->rows(4)
                            ->readOnly()
                            ->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('Detalles del Técnico')
                    ->schema([
                        Forms\Components\TextInput::make('technician.name')
                            ->label('Técnico Responsable')
                            ->readOnly(),
                        Forms\Components\DateTimePicker::make('fecha_fin_atencion')
                            ->label('Fecha Reporte')
                            ->displayFormat('d/m/Y H:i A')
                            ->readOnly(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_orden')
                    ->label('N° Orden')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('cliente.name')
                    ->label('Cliente')
                    ->searchable()
                    ->toggleable(),
                BadgeColumn::make('solucion_tecnico')
                    ->label('Tipo Solicitud')
                    ->formatStateUsing(function ($state) {
                        $str = is_array($state) ? implode(' ', $state) : $state;
                        if (str_contains($str, 'Reprogramar'))
                            return 'Reprogramación';
                        if (str_contains($str, 'Solicitar Cierre'))
                            return 'Solicitud Cierre';
                        return 'Otro';
                    })
                    ->colors([
                        'warning' => fn($state) => str_contains(is_array($state) ? implode($state) : $state, 'Reprogramar'),
                        'danger' => fn($state) => str_contains(is_array($state) ? implode($state) : $state, 'Solicitar Cierre'),
                    ])
                    ->toggleable(),
                TextColumn::make('observaciones')
                    ->label('Motivo')
                    ->limit(50)
                    ->tooltip(fn(Orden $record): string => $record->observaciones ?? '')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('technician.name')
                    ->label('Técnico')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
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
                    ->relationship('technician', 'name', fn(Builder $query) => $query->whereHas('roles', fn($q) => $q->where('name', 'tecnico')))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('tipo_orden')
                    ->label('Tipo de Orden')
                    ->options(Orden::TIPO_ORDEN_OPTIONS)
                    ->searchable(),
                SelectFilter::make('clasificacion')
                    ->label('Clasificación')
                    ->options([
                        'rapidas' => 'Rápidas',
                        'cuadrilla' => 'Cuadrilla',
                    ]),
                SelectFilter::make('solicitud_suscriptor')
                    ->label('Reporte')
                    ->options(Orden::SOLICITUD_SUSCRIPTOR_OPTIONS)
                    ->searchable(),
                Filter::make('fecha_trn')
                    ->form([
                        DatePicker::make('fecha_desde')->label('Fecha Desde'),
                        DatePicker::make('fecha_hasta')->label('Fecha Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['fecha_desde'],
                                fn(Builder $query, $date) => $query->whereDate('fecha_trn', '>=', $date),
                            )
                            ->when(
                                $data['fecha_hasta'],
                                fn(Builder $query, $date) => $query->whereDate('fecha_trn', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    // Allow viewing the full order PDF if needed
                    Tables\Actions\Action::make('pdf')
                        ->label('PDF')
                        ->icon('heroicon-o-document')
                        ->url(fn(Orden $record) => route('orden.pdf.stream', $record))
                        ->openUrlInNewTab(),

                    Action::make('reasignarTecnico')
                        ->label('Reasignar Técnico')
                        ->icon('heroicon-o-user-plus')
                        ->color('warning')
                        ->form([
                            Select::make('technician_id')
                                ->label('Nuevo Técnico')
                                ->relationship('technician', 'name', fn(Builder $query) => $query->whereHas('roles', fn($q) => $q->where('name', 'tecnico')))
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function (Orden $record, array $data) {
                            $record->update([
                                'technician_id' => $data['technician_id'],
                                'fecha_asignacion' => now(),
                            ]);

                            Notification::make()
                                ->title('Técnico reasignado correctamente')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('cerrarOrden')
                        ->label('Cerrar Orden')
                        ->icon('heroicon-o-lock-closed')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Cerrar Orden Especial')
                        ->modalSubheading('¿Está seguro que desea cerrar esta orden? Se marcará como cerrada y se registrará la fecha actual.')
                        ->action(function (Orden $record) {
                            $record->update([
                                'estado_orden' => Orden::ESTADO_CERRADA,
                                'fecha_cierre' => now(),
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->title('Orden cerrada correctamente')
                                ->success()
                                ->send();
                        }),
                ])
            ])
            ->bulkActions([

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdenEspecials::route('/'),
            // Using same resource for edit just to view the read-only form
            'edit' => Pages\EditOrdenEspecial::route('/{record}/edit'),
        ];
    }
}
