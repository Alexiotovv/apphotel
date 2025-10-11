@extends('layouts.app')

@section('content')
<h2>{{ isset($habitacion) ? 'Editar Habitación' : 'Nueva Habitación' }}</h2>

<form method="POST" action="{{ isset($habitacion) ? route('habitaciones.update', $habitacion->id) : route('habitaciones.store') }}">
    @csrf
    @if(isset($habitacion))
        @method('PUT')
    @endif

    <div class="mb-3">
        <label>Tipo de habitación</label>
        <input type="text" name="tipo" class="form-control" value="{{ old('tipo', $habitacion->tipo ?? '') }}" required>
    </div>
    <div class="mb-3">
        <label>Descripción</label>
        <textarea name="descripcion" class="form-control" rows="3" required>{{ old('descripcion', $habitacion->descripcion ?? '') }}</textarea>
    </div>
    <div class="mb-3">
        <label>Capacidad (personas)</label>
        <input type="number" name="capacidad" class="form-control" value="{{ old('capacidad', $habitacion->capacidad ?? 1) }}" min="1" required>
    </div>
    <div class="mb-3">
        <label>Precio por noche ($)</label>
        <input type="number" step="0.01" name="precio_noche" class="form-control" value="{{ old('precio_noche', $habitacion->precio_noche ?? 0) }}" min="0" required>
    </div>
    <div class="mb-3">
        <label>Disponible</label>
        <select name="disponible" id="disponible" class="form-select">
            <option value=1>Si</option>
            <option value=0>No</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">{{ isset($habitacion) ? 'Actualizar' : 'Crear' }}</button>
    <a href="{{ route('habitaciones.index') }}" class="btn btn-secondary">Cancelar</a>
</form>
@endsection