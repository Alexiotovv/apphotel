@extends('layouts.cliente')

@section('title', 'Detalles de Reserva')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-file-invoice me-2"></i>Detalles de Reserva
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('cliente.reservas.index') }}">Mis Reservas</a></li>
                <li class="breadcrumb-item active">Reserva #{{ str_pad($reserva->id, 6, '0', STR_PAD_LEFT) }}</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="{{ route('cliente.reservas.index') }}" class="btn btn-secondary">
            ↩️ Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Información de la Reserva -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información de la Reserva</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6>📋 Número de Reserva</h6>
                        <h3 class="text-primary">#{{ str_pad($reserva->id, 6, '0', STR_PAD_LEFT) }}</h3>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6>📊 Estado</h6>
                        @if($reserva->estado == 'pendiente')
                            <span class="badge-estado badge-pendiente">⏳ Pendiente</span>
                        @elseif($reserva->estado == 'confirmada')
                            <span class="badge-estado badge-confirmada">✅ Confirmada</span>
                        @else
                            <span class="badge-estado badge-cancelada">❌ Cancelada</span>
                        @endif
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6>📅 Fechas de Estadía</h6>
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
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6>👨‍👩‍👧‍👦 Huéspedes</h6>
                        <div class="alert alert-light">
                            <p class="mb-1"><i class="fas fa-user"></i> 
                                <strong>Adultos:</strong> {{ $reserva->adultos }}
                            </p>
                            @if($reserva->ninos > 0)
                                <p class="mb-0"><i class="fas fa-child"></i> 
                                    <strong>Niños:</strong> {{ $reserva->ninos }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <h6>📝 Notas</h6>
                <div class="alert alert-light">
                    {{ $reserva->notas ?? 'No hay notas adicionales.' }}
                </div>
            </div>
        </div>

        <!-- Información de Pago -->
        @if($reserva->venta)
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Información de Pago</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6>💰 Total a Pagar</h6>
                        <h2 class="text-success">${{ number_format($reserva->precio_total, 2) }}</h2>
                        <small class="text-muted">
                            {{ $reserva->noches }} noches × ${{ number_format($reserva->habitacion->precio_noche, 2) }}
                        </small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6>💳 Estado de Pago</h6>
                        @if($reserva->venta->estado == 'completada')
                            <span class="badge bg-success">💰 Pagado</span><br>
                            <small>Método: {{ $reserva->venta->metodo_pago }}</small>
                        @elseif($reserva->venta->estado == 'pendiente')
                            <span class="badge bg-warning">💳 Pendiente</span>
                        @else
                            <span class="badge bg-danger">❌ Cancelado</span>
                        @endif
                    </div>
                </div>
                
                @if($reserva->venta && $reserva->venta->pagos->count() > 0)
                    <div class="card mt-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historial de Pagos</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Método</th>
                                            <th>Referencia</th>
                                            <th>Monto</th>
                                            <th>Estado</th>
                                            <th>Detalles</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reserva->venta->pagos as $pago)
                                        <tr>
                                            <td>{{ $pago->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ ucfirst($pago->metodo_pago) }}</td>
                                            <td><code>{{ $pago->referencia }}</code></td>
                                            <td>${{ number_format($pago->monto, 2) }}</td>
                                            <td>
                                                @if($pago->estado == 'completado')
                                                    <span class="badge bg-success">Completado</span>
                                                @elseif($pago->estado == 'pendiente')
                                                    <span class="badge bg-warning">Pendiente</span>
                                                @else
                                                    <span class="badge bg-danger">Rechazado</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($pago->detalles)
                                                    <button class="btn btn-sm btn-outline-info" 
                                                            onclick="alert(JSON.stringify({{ json_encode($pago->detalles) }}, null, 2))">
                                                        Ver
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Habitación -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-bed me-2"></i>Habitación Reservada</h5>
            </div>
            <div class="card-body">
                <h5>{{ $reserva->habitacion->tipo }}</h5>
                <p class="text-muted">{{ $reserva->habitacion->descripcion }}</p>
                
                <hr>
                
                <p><i class="fas fa-users"></i> <strong>Capacidad:</strong> {{ $reserva->habitacion->capacidad }} personas</p>
                <p><i class="fas fa-money-bill"></i> <strong>Precio por noche:</strong> ${{ number_format($reserva->habitacion->precio_noche, 2) }}</p>
                
                @if($reserva->habitacion->foto)
                    <img src="{{ asset('storage/habitaciones/' . $reserva->habitacion->foto) }}" 
                         alt="{{ $reserva->habitacion->tipo }}" 
                         class="img-fluid rounded mt-2">
                @endif
            </div>
        </div>

        <!-- Acciones -->
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Acciones</h5>
            </div>
            <div class="card-body">
                @if($reserva->estado == 'pendiente')
                    <a href="{{ route('cliente.reservas.pagar', $reserva->id) }}" 
                       class="btn btn-success w-100 mb-2">
                        💳 Proceder al Pago
                    </a>
                    
                    <form action="{{ route('cliente.reservas.cancelar', $reserva->id) }}" 
                          method="POST" onsubmit="return confirm('¿Cancelar esta reserva?')">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100 mb-2">
                            ❌ Cancelar Reserva
                        </button>
                    </form>
                @endif
                
                @if($reserva->estado == 'confirmada' && $reserva->venta && $reserva->venta->estado == 'completada')
                    <a href="{{ route('cliente.reservas.comprobante', optional($reserva->venta->pagos->first())->id) }}" 
                       class="btn btn-secondary w-100 mb-2">
                        🧾 Ver Comprobante
                    </a>
                    
                    <form action="{{ route('cliente.reservas.factura', $reserva->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-info w-100 mb-2">
                            📄 Solicitar Factura
                        </button>
                    </form>
                @endif
                
                <a href="https://wa.me/56912345678?text=Hola,%20tengo%20una%20consulta%20sobre%20mi%20reserva%20{{ $reserva->id }}" 
                   class="btn btn-outline-primary w-100" target="_blank">
                    💬 Consultar por WhatsApp
                </a>
            </div>
        </div>

        <!-- Información de Contacto -->
        <div class="alert alert-info mt-4">
            <h6><i class="fas fa-phone-alt me-1"></i> Contacto</h6>
            <p class="mb-1"><strong>Teléfono:</strong> +56 9 1234 5678</p>
            <p class="mb-1"><strong>Email:</strong> reservas@hotelici.com</p>
            <p class="mb-0"><strong>Horario:</strong> 24/7</p>
        </div>
    </div>
</div>
@endsection