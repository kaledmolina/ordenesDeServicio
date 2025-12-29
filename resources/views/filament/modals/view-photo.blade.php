<div>
    @if($photo)
        <img src="{{ route('fotos.show', ['ordenFoto' => $photo]) }}" 
             alt="Vista previa de la foto" 
             class="w-full h-auto rounded-lg">
    @else
        <p class="text-center text-gray-500">No se pudo cargar la foto.</p>
    @endif
</div>