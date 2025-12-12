@extends('layouts.cliente')

@section('title', 'Historial de Pagos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-history me-2"></i>Historial de Pagos
    </h1>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">${{ number_format($estadisticas['total_pagado'], 2) }}</h3>
                        <small>Total Pagado</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">{{ $estadisticas['pagos_completados'] }}</h3>
                        <small>Pagos Completados</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">{{ $estadisticas['pagos_pendientes'] }}</h3>
                        <small>Pagos Pendientes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Listado de Pagos -->
<div class="card">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Mis Pagos</h5>
    </div>
    <div class="card-body">
        @if($ventas->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Reserva</th>
                            <th>Método</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ventas as $venta)
                            @foreach($venta->pagos as $pago)
                            <tr>
                                <td>{{ $pago->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($venta->reserva)
                                        #{{ str_pad($venta->reserva->id, 6, '0', STR_PAD_LEFT) }}<br>
                                        <small>{{ $venta->reserva->habitacion->tipo ?? 'N/A' }}</small>
                                    @else
                                        Sin reserva
                                    @endif
                                </td>
                                <td>
                                    @if($pago->metodo_pago == 'tarjeta')
                                        <i class="fas fa-credit-card me-1"></i> Tarjeta
                                    @elseif($pago->metodo_pago == 'qr')
                                        <i class="fas fa-qrcode me-1"></i> QR
                                    @else
                                        {{ ucfirst($pago->metodo_pago) }}
                                    @endif
                                </td>
                                <td>${{ number_format($pago->monto, 2) }}</td>
                                <td>
                                    @if($pago->estado == 'completado')
                                        <span class="badge bg-success">Completado</span>
                                    @elseif($pago->estado == 'pendiente')
                                        <span class="badge bg-warning">Pendiente</span>
                                    @elseif($pago->estado == 'reembolsado')
                                        <span class="badge bg-info">Reembolsado</span>
                                    @else
                                        <span class="badge bg-danger">{{ ucfirst($pago->estado) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('cliente.pagos.show', $pago->id) }}" 
                                           class="btn btn-info" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($pago->estado == 'completado')
                                        <a href="{{ route('cliente.reservas.comprobante', $pago->id) }}" 
                                           class="btn btn-secondary" title="Comprobante">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <div class="d-flex justify-content-center">
                {{ $ventas->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-money-bill-wave fa-4x text-muted"></i>
                </div>
                <h4>No tienes pagos registrados</h4>
                <p class="text-muted">Tus pagos aparecerán aquí una vez que los realices.</p>
            </div>
        @endif
    </div>
</div>
@endsection