@extends('layouts.app')

@section('content')
<h2>{{ isset($servicio) ? 'Editar Servicio' : 'Nuevo Servicio' }}</h2>

<form method="POST" action="{{ isset($servicio) ? route('servicios.update', $servicio->id) : route('servicios.store') }}">
    @csrf
    @if(isset($servicio))
        @method('PUT')
    @endif

    <div class="mb-3">
        <label>Nombre del servicio</label>
        <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $servicio->nombre ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label>Descripción</label>
        <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $servicio->descripcion ?? '') }}</textarea>
    </div>

    <div class="mb-3">
        <label>Precio ($)</label>
        <input type="number" step="0.01" name="precio" class="form-control" value="{{ old('precio', $servicio->precio ?? 0) }}" min="0" required>
    </div>

    <div class="mb-3">
        <label>Disponible</label>
        <select name="disponible" class="form-select">
            <option value="1" {{ (old('disponible', $servicio->disponible ?? true) ? 'selected' : '') }}>Sí</option>
            <option value="0" {{ (!old('disponible', $servicio->disponible ?? true) ? 'selected' : '') }}>No</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">{{ isset($servicio) ? 'Actualizar' : 'Crear' }}</button>
    <a href="{{ route('servicios.index') }}" class="btn btn-secondary">Cancelar</a>
</form>
@endsection