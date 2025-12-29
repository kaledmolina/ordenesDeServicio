<x-filament-panels::page>
    <div class="p-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            Seleccione un Rango de Fechas
        </h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Elija una fecha de inicio y una fecha de fin para generar el reporte de órdenes en formato Excel.
        </p>

        {{-- El formulario se vincula con el método `export` de la clase --}}
        <form wire:submit="export" class="mt-6 space-y-6">
            {{-- Esto renderiza los campos DatePicker definidos en la clase --}}
            {{ $this->form }}

            <div>
                <x-filament::button type="submit" icon="heroicon-o-document-arrow-down">
                    Descargar Reporte
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>