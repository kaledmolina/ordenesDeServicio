<x-filament-panels::page>
    {{-- El header ha sido eliminado como se solicitó. 
         Filament se encargará de mostrar el título y las acciones de la cabecera definidos en la clase de la página. --}}

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-6">
        @forelse ($record->fotos as $foto)
            {{-- Tarjeta con clases para tema claro y oscuro --}}
            <div class="flex flex-col border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-md">
                
                {{-- Imagen Clickeable --}}
                <div 
                    wire:click="mountAction('viewPhotoAction', { photoId: {{ $foto->id }} })"
                    class="cursor-pointer flex-grow"
                >
                     <img src="{{ route('fotos.show', ['ordenFoto' => $foto]) }}"
                         alt="Foto de la orden"
                         class="object-cover w-full h-48 transition-transform duration-300 hover:scale-105">
                </div>
                
                {{-- Contenedor del botón con clases para tema claro y oscuro --}}
                <div class="p-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex justify-end">
                    {{-- Llama a la acción de eliminar, que ahora se renderiza como un botón completo --}}
                    {{ $this->deletePhotoAction()->arguments(['photoId' => $foto->id]) }}
                </div>
            </div>
        @empty
            {{-- Mensaje si no hay fotos con clases para tema claro y oscuro --}}
            <div class="col-span-full text-center py-12">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                     <x-heroicon-o-photo class="h-6 w-6 text-gray-500 dark:text-gray-400" />
                </div>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No hay fotos</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Aún no se han subido fotos para esta orden.</p>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>