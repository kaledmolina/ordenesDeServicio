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

class OrdenEspecialResource extends Resource
{
    protected static ?string $model = Orden::class;
    protected static ?string $slug = 'ordenes-especiales';

    protected static ?string $navigationLabel = 'Órdenes Especiales';
    protected static ?string $modelLabel = 'Orden Especial';

    protected static ?string $navigationGroup = 'Gestión de Órdenes';
    protected static ?int $navigationSort = 2; // Below standard orders

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function ($query) {
                $query->where('solucion_tecnico', 'like', '%Reprogramar%')
                      ->orWhere('solucion_tecnico', 'like', '%Solicitar Cierre%');
            });
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
                            ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state)
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
                        Forms\Components\TextInput::make('fecha_fin_atencion')
                            ->label('Fecha Reporte')
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
                    ->sortable(),
                TextColumn::make('cliente.name')
                    ->label('Cliente')
                    ->searchable(),
                BadgeColumn::make('solucion_tecnico')
                    ->label('Tipo Solicitud')
                    ->formatStateUsing(function ($state) {
                        $str = is_array($state) ? implode(' ', $state) : $state;
                        if (str_contains($str, 'Reprogramar')) return 'Reprogramación';
                        if (str_contains($str, 'Solicitar Cierre')) return 'Solicitud Cierre';
                        return 'Otro';
                    })
                    ->colors([
                        'warning' => fn ($state) => str_contains(is_array($state)?implode($state):$state, 'Reprogramar'),
                        'danger' => fn ($state) => str_contains(is_array($state)?implode($state):$state, 'Solicitar Cierre'),
                    ]),
                TextColumn::make('observaciones')
                    ->label('Motivo')
                    ->limit(50)
                    ->tooltip(fn (Orden $record): string => $record->observaciones ?? '')
                    ->searchable(),
                TextColumn::make('technician.name')
                    ->label('Técnico')
                    ->searchable(),
                TextColumn::make('updated_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Allow viewing the full order PDF if needed
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document')
                    ->url(fn(Orden $record) => route('orden.pdf.stream', $record))
                    ->openUrlInNewTab(),
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
