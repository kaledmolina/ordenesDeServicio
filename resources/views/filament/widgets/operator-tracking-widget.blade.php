<x-filament-widgets::widget>
    <x-filament::section>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            {{-- Operators List --}}
            <div>
                <h2 class="mb-4 text-lg font-bold">Seguimiento de Operadores</h2>
                <div class="space-y-4">
                    @forelse ($operators as $operator)
                        <div class="flex items-center justify-between p-4 bg-white border rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                            <div class="flex items-center space-x-3">
                                <div class="relative">
                                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-bold text-gray-600">{{ substr($operator->name, 0, 2) }}</span>
                                    </div>
                                    <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full {{ $operator->activeOrder ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $operator->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        @if($operator->activeOrder)
                                            En Orden: #{{ $operator->activeOrder->numero_orden }}
                                        @else
                                            Disponible
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @if($operator->activeOrder)
                                <div class="text-right">
                                    <span class="text-xs font-semibold text-green-600 bg-green-100 px-2 py-1 rounded-full dark:bg-green-900 dark:text-green-300">
                                        En Proceso
                                    </span>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $operator->activeOrder->fecha_inicio_atencion?->diffForHumans() ?? 'Recién iniciado' }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500">No hay operadores registrados.</p>
                    @endforelse
                </div>
            </div>

            {{-- Unassigned Orders --}}
            <div>
                <h2 class="mb-4 text-lg font-bold">Órdenes Sin Asignar</h2>
                <div class="space-y-4">
                    @forelse ($unassignedOrders as $order)
                        <div class="p-4 bg-white border rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="font-bold text-gray-900 dark:text-gray-100">#{{ $order->numero_orden }}</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-300"> - {{ $order->tipo_orden }}</span>
                                </div>
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300">
                                    Pendiente
                                </span>
                            </div>
                            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                <p>{{ $order->direccion }}</p>
                                <p class="mt-1 text-xs">Creada: {{ $order->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                     @empty
                        <p class="text-gray-500">No hay órdenes sin asignar.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
