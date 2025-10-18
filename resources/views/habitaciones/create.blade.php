@extends('layouts.app')

@section('content')

<form method="POST" 
      action="{{ route('habitaciones.store') }}" 
      enctype="multipart/form-data">
    @csrf

    <div class="mb-3">
        <label>Tipo de habitación</label>
        <input type="text" name="tipo" class="form-control" value="{{ old('tipo') }}" required>
    </div>
    <div class="mb-3">
        <label>Descripción</label>
        <textarea name="descripcion" class="form-control" rows="3" required>{{ old('descripcion') }}</textarea>
    </div>
    <div class="mb-3">
        <label>Capacidad (personas)</label>
        <input type="number" name="capacidad" class="form-control" value="{{ old('capacidad', 1) }}" min="1" required>
    </div>
    <div class="mb-3">
        <label>Precio por noche ($)</label>
        <input type="number" step="0.01" name="precio_noche" class="form-control" value="{{ old('precio_noche', 0) }}" min="0" required>
    </div>
    <div class="mb-3">
        <label>Disponible</label>
        <select name="disponible" id="disponible" class="form-select">
            <option value="1" {{ old('disponible') == 1 ? 'selected' : '' }}>Sí</option>
            <option value="0" {{ old('disponible') == 0 ? 'selected' : '' }}>No</option>
        </select>
    </div>
    <div class="mb-3">
        <label>Foto de la habitación</label>
        <input type="file" name="foto" class="form-control" accept="image/*">
    </div>
    <button type="submit" class="btn btn-primary">Crear</button>
    <a href="{{ route('habitaciones.index') }}" class="btn btn-secondary">Cancelar</a>
</form>

@endsection
