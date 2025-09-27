@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🏨 Habitaciones</h2>
    <a href="{{ route('habitaciones.create') }}" class="btn btn-success">➕ Nueva Habitación</a>
</div>

<div class="table-container">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Capacidad</th>
                <th>Precio</th>
                <th>Disponible</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($habitaciones as $h)
            <tr>
                <td>{{ $h->tipo }}</td>
                <td>{{ $h->capacidad }} personas</td>
                <td>${{ number_format($h->precio_noche, 2) }}</td>
                <td>{!! $h->disponible ? '<span class="text-success">✅</span>' : '<span class="text-danger">❌</span>' !!}</td>
                <td>
                    <a href="{{ route('habitaciones.edit', $h->id) }}" class="btn btn-sm btn-warning">✏️</a>
                    <form action="{{ route('habitaciones.destroy', $h->id) }}" method="POST" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar?')">🗑️</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center">No hay habitaciones registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection