@extends('layouts.app')

@section('content')
<h2>Lista de Servicios</h2>

<a href="{{ route('servicios.create') }}" class="btn btn-success mb-3">Agregar Servicio</a>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Disponible</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($servicios as $servicio)
        <tr>
            <td>{{ $servicio->nombre }}</td>
            <td>{{ Str::limit($servicio->descripcion, 50) }}</td>
            <td>${{ number_format($servicio->precio, 2) }}</td>
            <td>{{ $servicio->disponible ? 'Sí' : 'No' }}</td>
            <td>
                <a href="{{ route('servicios.edit', $servicio) }}" class="btn btn-sm btn-warning">Editar</a>
                <form action="{{ route('servicios.destroy', $servicio) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este servicio?')">Eliminar</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">No hay servicios registrados.</td>
        </tr>
        @endforelse
    </tbody>
</table>
@endsection