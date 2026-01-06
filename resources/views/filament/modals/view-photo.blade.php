<div>
    @if($photo)
        <img src="{{ \Illuminate\Support\Facades\Storage::url($photo->path) }}" 
             alt="Vista previa de la foto" 
             class="w-full h-auto rounded-lg">
    @else
        <p class="text-center text-gray-500">No se pudo cargar la foto.</p>
    @endif
</div>