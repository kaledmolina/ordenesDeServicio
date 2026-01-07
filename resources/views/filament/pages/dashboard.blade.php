<x-filament-panels::page>
    @livewire(\App\Filament\Widgets\OrderStatsOverview::class)

    <div class="mt-6 grid grid-cols-1 gap-6">
        @livewire(\App\Filament\Widgets\RankingTecnicosMes::class)
    </div>

    {{-- Renderiza el widget de la tabla en un nuevo contenedor, forzándolo a ocupar todo el ancho. --}}
    <div class="mt-6">
        @livewire(\App\Filament\Widgets\FilteredOrdersTable::class, ['status' => $this->status])
    </div>
</x-filament-panels::page>