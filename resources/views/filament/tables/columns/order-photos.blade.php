<div class="flex -space-x-2 overflow-hidden">
    @php
        $fotos = $getRecord()->fotos;
    @endphp
    <!-- Debug info: Total {{ $fotos->count() }} photos. First path: {{ $fotos->first()?->path ?? 'none' }} -->
    @foreach($fotos->take(3) as $foto)
        <img 
            src="{{ route('fotos.show', ['ordenFoto' => $foto->id]) }}" 
            alt="Foto de la orden"
            class="inline-block h-10 w-10 rounded-full ring-2 ring-white object-cover"
            title="ID: {{ $foto->id }}"
        >
        <!-- Debug Url: {{ route('fotos.show', ['ordenFoto' => $foto->id]) }} -->
    @endforeach
    @if($fotos->count() > 3)
        <div class="flex items-center justify-center h-10 w-10 rounded-full ring-2 ring-white bg-gray-100 text-xs font-medium text-gray-600">
            +{{ $fotos->count() - 3 }}
        </div>
    @endif
</div>
