@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">📄 Detalles de Reserva</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.reservas.index') }}">Reservas</a></li>
                    <li class="breadcrumb-item active">Reserva #{{ str_pad($reserva->id, 6, '0', STR_PAD_LEFT) }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.reservas.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información Principal -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información de la Reserva</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Número de Reserva:</strong></p>
                            <h2 class="text-primary">#{{ str_pad($reserva->id, 6, '0', STR_PAD_LEFT) }}</h2>
                            
                            <p><strong>Estado:</strong></p>
                            <span class="estado-badge estado-{{ $reserva->estado }}">
                                {{ ucfirst($reserva->estado) }}
                            </span>
                            
                            <p class="mt-3"><strong>Fecha de Reserva:</strong></p>
                            <p>{{ $reserva->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Fechas de Estadía:</strong></p>
                            <div class="alert alert-light">
                                <p class="mb-1"><i class="fas fa-sign-in-alt text-primary"></i> 
                                    <strong>Entrada:</strong> {{ $reserva->fecha_entrada->format('d/m/Y') }}
                                </p>
                                <p class="mb-1"><i class="fas fa-sign-out-alt text-secondary"></i> 
                                    <strong>Salida:</strong> {{ $reserva->fecha_salida->format('d/m/Y') }}
                                </p>
                                <p class="mb-0"><i class="fas fa-moon text-info"></i> 
                                    <strong>Noches:</strong> {{ $reserva->noches }}
                                </p>
                            </div>
                            
                            <p><strong>Huéspedes:</strong></p>
                            <p>
                                <i class="fas fa-user"></i> {{ $reserva->adultos }} adulto(s)
                                @if($reserva->ninos > 0)
                                    <br><i class="fas fa-child"></i> {{ $reserva->ninos }} niño(s)
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del Cliente -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Información del Cliente</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre Completo:</strong></p>
                            <p class="h5">{{ $reserva->cliente->nombre }} {{ $reserva->cliente->apellido }}</p>
                            
                            <p><strong>DNI:</strong></p>
                            <p>{{ $reserva->cliente->dni }}</p>
                            
                            <p><strong>Dirección:</strong></p>
                            <p>{{ $reserva->cliente->direccion ?? 'No registrada' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Contacto:</strong></p>
                            <p><i class="fas fa-envelope"></i> {{ $reserva->cliente->email }}</p>
                            <p><i class="fas fa-phone"></i> {{ $reserva->cliente->telefono }}</p>
                            
                            <p><strong>Cliente desde:</strong></p>
                            <p>{{ $reserva->cliente->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Habitación -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-bed me-2"></i>Habitación Reservada</h5>
                </div>
                <div class="card-body">
                    <p class="h5">{{ $reserva->habitacion->tipo }}</p>
                    <p class="text-muted">{{ $reserva->habitacion->descripcion }}</p>
                    
                    <hr>
                    
                    <p><strong>Precio por noche:</strong></p>
                    <p class="h4">${{ number_format($reserva->habitacion->precio_noche, 2) }}</p>
                    
                    <p><strong>Capacidad:</strong> {{ $reserva->habitacion->capacidad }} personas</p>
                    <p><strong>Disponibilidad:</strong> 
                        <span class="badge {{ $reserva->habitacion->disponible ? 'bg-success' : 'bg-danger' }}">
                            {{ $reserva->habitacion->disponible ? 'Disponible' : 'Ocupada' }}
                        </span>
                    </p>
                    
                    @if($reserva->habitacion->foto)
                        <img src="{{ asset('storage/habitaciones/' . $reserva->habitacion->foto) }}" 
                             alt="{{ $reserva->habitacion->tipo }}" 
                             class="img-fluid rounded mt-2">
                    @endif
                </div>
            </div>

            <!-- Información Financiera -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Información Financiera</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <p class="mb-1">Total a Pagar</p>
                        <h2 class="text-success">${{ number_format($reserva->precio_total, 2) }}</h2>
                        <small class="text-muted">
                            {{ $reserva->noches }} noches × ${{ number_format($reserva->habitacion->precio_noche, 2) }}
                        </small>
                    </div>
                    
                    <hr>
                    
                    @if($reserva->venta)
                        <p><strong>Venta Asociada:</strong></p>
                        <p>ID: V-{{ str_pad($reserva->venta->id, 6, '0', STR_PAD_LEFT) }}</p>
                        <p><strong>Estado de Pago:</strong></p>
                        <span class="badge {{ $reserva->venta->estado == 'completada' ? 'bg-success' : 'bg-warning' }}">
                            {{ ucfirst($reserva->venta->estado) }}
                        </span>
                        <p class="mt-2"><strong>Método de Pago:</strong></p>
                        <p>{{ ucfirst($reserva->venta->metodo_pago) }}</p>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> No hay venta asociada
                        </div>
                    @endif
                </div>
            </div>

            <!-- Notas -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notas</h5>
                </div>
                <div class="card-body">
                    @if($reserva->notas)
                        <p><strong>Notas del Cliente:</strong></p>
                        <div class="alert alert-light">
                            {{ $reserva->notas }}
                        </div>
                    @endif
                    
                    @if($reserva->notas_admin)
                        <p><strong>Notas del Administrador:</strong></p>
                        <div class="alert alert-info">
                            {{ $reserva->notas_admin }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <div>
                    <a href="{{ route('admin.reservas.edit', $reserva->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar Reserva
                    </a>
                    @if($reserva->estado == 'pendiente')
                        <form action="{{ route('admin.reservas.confirmar', $reserva->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Confirmar Reserva
                            </button>
                        </form>
                        <form action="{{ route('admin.reservas.cancelar', $reserva->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger" 
                                    onclick="return confirm('¿Cancelar esta reserva?')">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        </form>
                    @endif
                </div>
                <div>
                    <form action="{{ route('admin.reservas.destroy', $reserva->id) }}" method="POST" 
                          onsubmit="return confirm('¿Eliminar definitivamente esta reserva?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-dark">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.estado-badge {
    padding: 8px 20px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
}
.estado-pendiente { background: #fff3cd; color: #856404; }
.estado-confirmada { background: #d4edda; color: #155724; }
.estado-cancelada { background: #f8d7da; color: #721c24; }
</style>
@endsection