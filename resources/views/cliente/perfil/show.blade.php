@extends('layouts.cliente')

@section('title', 'Mi Perfil')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <!-- Información del Usuario -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Mi Perfil</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <div class="avatar-circle mb-3">
                        <i class="fas fa-user fa-3x text-muted"></i>
                    </div>
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted mb-0">{{ $cliente->email }}</p>
                    <p class="text-muted">Cliente desde: {{ $cliente->created_at->format('d/m/Y') }}</p>
                </div>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('cliente.perfil.edit') }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Editar Perfil
                    </a>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Mis Estadísticas</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <p class="mb-1"><strong>Total Reservas:</strong></p>
                    <h4 class="text-primary">{{ $estadisticas['total_reservas'] }}</h4>
                </div>
                <div class="mb-3">
                    <p class="mb-1"><strong>Reservas Confirmadas:</strong></p>
                    <h4 class="text-success">{{ $estadisticas['reservas_confirmadas'] }}</h4>
                </div>
                <div class="mb-3">
                    <p class="mb-1"><strong>Total Gastado:</strong></p>
                    <h4 class="text-success">${{ number_format($estadisticas['total_gastado'], 2) }}</h4>
                </div>
                @if($estadisticas['ultima_reserva'])
                <div class="mb-0">
                    <p class="mb-1"><strong>Última Reserva:</strong></p>
                    <p class="mb-0">
                        {{ $estadisticas['ultima_reserva']->created_at->format('d/m/Y') }}<br>
                        <small>{{ $estadisticas['ultima_reserva']->habitacion->tipo ?? 'N/A' }}</small>
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Información Personal -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Información Personal</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <p class="mb-1"><strong>Nombre Completo:</strong></p>
                        <p>{{ $cliente->nombre }} {{ $cliente->apellido }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="mb-1"><strong>DNI:</strong></p>
                        <p>{{ $cliente->dni }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <p class="mb-1"><strong>Teléfono:</strong></p>
                        <p>{{ $cliente->telefono }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="mb-1"><strong>Email:</strong></p>
                        <p>{{ $cliente->email }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <p class="mb-1"><strong>Dirección:</strong></p>
                        <p>{{ $cliente->direccion ?? 'No registrada' }}</p>
                    </div>
                </div>
                @if($cliente->fecha_nacimiento)
                <div class="row">
                    <div class="col-12">
                        <p class="mb-1"><strong>Fecha de Nacimiento:</strong></p>
                        <p>{{ \Carbon\Carbon::parse($cliente->fecha_nacimiento)->format('d/m/Y') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Seguridad</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('cliente.perfil.password') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Contraseña Actual *</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nueva Contraseña *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirmar Nueva Contraseña *</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <small>La contraseña debe tener al menos 8 caracteres.</small>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key me-2"></i>Cambiar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection