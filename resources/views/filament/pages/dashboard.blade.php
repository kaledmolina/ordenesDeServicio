<x-filament-panels::page>
    @livewire(\App\Filament\Widgets\OrderStatsOverview::class)



    {{-- Renderiza el widget de la tabla en un nuevo contenedor, forzándolo a ocupar todo el ancho. --}}
    <div class="mt-6">
        @if($this->status && $this->status !== 'todas')
            @livewire(\App\Filament\Widgets\FilteredOrdersTable::class, ['status' => $this->status])
        @else
            @livewire(\App\Filament\Widgets\TechnicianLocationTable::class)
        @endif
    </div>
</x-filament-panels::page>