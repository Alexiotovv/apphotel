@extends('layouts.app')

@section('content')
<h2>Editar Servicio</h2>

<form method="POST" action="{{ route('servicios.update', $servicio->id) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label>Nombre del servicio</label>
        <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $servicio->nombre) }}" required>
    </div>

    <div class="mb-3">
        <label>Descripción</label>
        <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $servicio->descripcion) }}</textarea>
    </div>

    <div class="mb-3">
        <label>Precio ($)</label>
        <input type="number" step="0.01" name="precio" class="form-control" value="{{ old('precio', $servicio->precio) }}" min="0" required>
    </div>

    <div class="mb-3">
        <label>Disponible</label>
        <select name="disponible" class="form-select">
            <option value="1" {{ ($servicio->disponible == 1) ? 'selected' : '' }}>Sí</option>
            <option value="0" {{ ($servicio->disponible == 0) ? 'selected' : '' }}>No</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Actualizar</button>
    <a href="{{ route('servicios.index') }}" class="btn btn-secondary">Cancelar</a>
</form>
@endsection