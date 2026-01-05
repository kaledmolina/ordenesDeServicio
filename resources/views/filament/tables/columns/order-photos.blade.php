<div class="flex -space-x-2 overflow-hidden">
    @foreach($getRecord()->fotos->take(3) as $foto)
        <img 
            src="{{ route('fotos.show', ['ordenFoto' => $foto->id]) }}" 
            alt="Foto de la orden"
            class="inline-block h-10 w-10 rounded-full ring-2 ring-white object-cover"
        >
    @endforeach
    @if($getRecord()->fotos->count() > 3)
        <div class="flex items-center justify-center h-10 w-10 rounded-full ring-2 ring-white bg-gray-100 text-xs font-medium text-gray-600">
            +{{ $getRecord()->fotos->count() - 3 }}
        </div>
    @endif
</div>
