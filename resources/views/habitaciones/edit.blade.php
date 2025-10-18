@extends('layouts.app')

@section('content')
<h2>Editar Habitación</h2>

<form method="POST" action="{{ route('habitaciones.update', $habitacion->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label>Tipo de habitación</label>
        <input type="text" name="tipo" class="form-control" value="{{ old('tipo', $habitacion->tipo) }}" required>
    </div>

    <div class="mb-3">
        <label>Descripción</label>
        <textarea name="descripcion" class="form-control" rows="3" required>{{ old('descripcion', $habitacion->descripcion) }}</textarea>
    </div>

    <div class="mb-3">
        <label>Capacidad (personas)</label>
        <input type="number" name="capacidad" class="form-control" value="{{ old('capacidad', $habitacion->capacidad) }}" min="1" required>
    </div>

    <div class="mb-3">
        <label>Precio por noche ($)</label>
        <input type="number" step="0.01" name="precio_noche" class="form-control" value="{{ old('precio_noche', $habitacion->precio_noche) }}" min="0" required>
    </div>

    <div class="mb-3">
        <label>Disponible</label>
        <select name="disponible" class="form-select">
            <option value="1" {{ ($habitacion->disponible == 1) ? 'selected' : '' }}>Sí</option>
            <option value="0" {{ ($habitacion->disponible == 0) ? 'selected' : '' }}>No</option>
        </select>
    </div>
    <div class="mb-3">
        <label>Foto de la habitación</label>
        <input type="file" name="foto" class="form-control" accept="image/*">
        @if(isset($habitacion) && $habitacion->foto)
            <div class="mt-2">
                <img src="{{ asset('storage/habitaciones/' . $habitacion->foto) }}" alt="Foto" width="150" class="rounded shadow">
            </div>
        @endif
    </div>

    <button type="submit" class="btn btn-primary">Actualizar</button>
    <a href="{{ route('habitaciones.index') }}" class="btn btn-secondary">Cancelar</a>
</form>
@endsection