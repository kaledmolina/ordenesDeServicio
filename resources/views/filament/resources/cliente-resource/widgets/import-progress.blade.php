<x-filament::widget>
    <div wire:poll.3s="updateProgress">
        @if($status === 'running')
            <x-filament::card>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm font-medium">
                        <span>Importando clientes...</span>
                        <span>{{ $progress }}%</span>
                    </div>

                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div class="bg-primary-600 h-2.5 rounded-full transition-all duration-500"
                            style="width: {{ $progress }}%"></div>
                    </div>

                    <p class="text-xs text-gray-500 text-center">
                        Procesados {{ $total > 0 ? round(($progress / 100) * $total) : 0 }} de {{ $total }} registros
                    </p>
                </div>
            </x-filament::card>
        @endif
    </div>
</x-filament::widget>