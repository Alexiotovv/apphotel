@extends('layouts.app')

@section('content')
    <h2>Editar Portada</h2>

    <form action="{{ route('portada.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="titulo" class="form-label">Título</label>
            <input type="text" class="form-control" id="titulo" name="titulo" value="{{ old('titulo', $pagina->titulo) }}" required>
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required>{{ old('descripcion', $pagina->descripcion) }}</textarea>
        </div>

        <div class="mb-3">
            <label for="foto" class="form-label">Foto de fondo (opcional)</label>
            <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
            @if($pagina->foto)
                <div class="mt-2">
                    <small>Imagen actual:</small><br>
                    <img src="{{ asset('storage/portada/' . $pagina->foto) }}" alt="Portada actual" style="max-height: 150px;">
                </div>
            @endif
        </div>

        <button type="submit" class="btn btn-primary">Actualizar Portada</button>
    </form>

@endsection