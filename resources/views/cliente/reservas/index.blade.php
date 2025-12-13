@extends('layouts.cliente')

@section('title', 'Mis Reservas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-calendar-alt me-2"></i>Mis Reservas
    </h1>
    <a href="{{ url('/') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nueva Reserva
    </a>
</div>

<!-- Estadísticas Rápidas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="fas fa-calendar fa-2x text-primary"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $reservas->where('estado', 'pendiente')->count() }}</h3>
                    <small class="text-muted">Pendientes</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $reservas->where('estado', 'confirmada')->count() }}</h3>
                    <small class="text-muted">Confirmadas</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="fas fa-bed fa-2x text-info"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $reservas->count() }}</h3>
                    <small class="text-muted">Total Reservas</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <i class="fas fa-money-bill-wave fa-2x text-warning"></i>
                </div>
                <div>
                    <h3 class="mb-0">${{ number_format($reservas->where('estado', 'confirmada')->sum('precio_total'), 2) }}</h3>
                    <small class="text-muted">Total Pagado</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Reservas -->
<div class="card">
    <div class="card-body">
        @if($reservas->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Habitación</th>
                            <th>Fechas</th>
                            <th>Total</th>
                            <th>Estado</th>
                            {{-- <th>Pago</th> --}}
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reservas as $reserva)
                            <tr>
                                <td>
                                    <strong>#{{ str_pad($reserva->id, 6, '0', STR_PAD_LEFT) }}</strong><br>
                                    <small class="text-muted">{{ $reserva->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    {{ $reserva->habitacion->tipo }}<br>
                                    <small>{{ $reserva->noches }} noche{{ $reserva->noches > 1 ? 's' : '' }}</small>
                                </td>
                                <td>
                                    <small>Entrada: {{ $reserva->fecha_entrada->format('d/m/Y') }}</small><br>
                                    <small>Salida: {{ $reserva->fecha_salida->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <strong>${{ number_format($reserva->precio_total, 2) }}</strong>
                                </td>
                                <td>
                                    @if($reserva->estado == 'pendiente')
                                        <span class="badge-estado badge-pendiente">⏳ Pendiente</span>
                                    @elseif($reserva->estado == 'confirmada')
                                        <span class="badge-estado badge-confirmada">✅ Confirmada</span>
                                    @else
                                        <span class="badge-estado badge-cancelada">❌ Cancelada</span>
                                    @endif
                                </td>
                                {{-- <td>
                                    @if($reserva->venta)
                                        @if($reserva->venta->estado == 'completada')
                                            <span class="badge bg-success">💰 Pagado</span>
                                        @elseif($reserva->venta->estado == 'pendiente')
                                            <span class="badge bg-warning">💳 Pendiente</span>
                                        @else
                                            <span class="badge bg-danger">❌ Cancelado</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">❓ Sin venta</span>
                                    @endif
                                </td> --}}
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('cliente.reservas.show', $reserva->id) }}" 
                                        class="btn btn-info" title="Ver detalles">
                                            👁️
                                        </a>
                                        
                                        @if($reserva->estado == 'pendiente')
                                            <a href="{{ route('cliente.reservas.pagar', $reserva->id) }}" 
                                            class="btn btn-success" title="Pagar">
                                                💳
                                            </a>
                                            <form action="{{ route('cliente.reservas.cancelar', $reserva->id) }}" 
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('¿Cancelar esta reserva?')">
                                                @csrf
                                                <button type="submit" class="btn btn-danger" title="Cancelar">
                                                    ❌
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if($reserva->estado == 'confirmada' && $reserva->venta && $reserva->venta->estado == 'completada')
                                            <a href="{{ route('cliente.reservas.comprobante', optional($reserva->venta->pagos->first())->id) }}" 
                                            class="btn btn-secondary" title="Ver comprobante">
                                                🧾
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <div class="d-flex justify-content-center">
                {{ $reservas->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-calendar-times fa-4x text-muted"></i>
                </div>
                <h4>No tienes reservas aún</h4>
                <p class="text-muted mb-4">Realiza tu primera reserva en nuestro hotel</p>
                <a href="{{ route('reservas.create') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-2"></i> Realizar Reserva
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Información de Contacto -->
<div class="alert alert-info mt-4">
    <h6><i class="fas fa-info-circle me-2"></i> ¿Necesitas ayuda?</h6>
    <p class="mb-0">
        Para consultas sobre tus reservas, contacta a nuestro servicio al cliente:<br>
        📞 <strong>+56 9 1234 5678</strong> | 
        ✉️ <strong>clientes@hotelici.com</strong>
    </p>
</div>
@endsection