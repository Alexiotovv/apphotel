@extends('layouts.cliente')

@section('title', 'Mis Facturas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">
        <i class="fas fa-file-invoice me-2"></i>Mis Facturas
    </h1>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-file-invoice-dollar fa-2x"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">${{ number_format($estadisticas['total_facturado'], 2) }}</h3>
                        <small>Total Facturado</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <div>
                        <h3 class="mb-0">{{ $estadisticas['facturas_pendientes'] }}</h3>
                        <small>Facturas Pendientes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Listado de Facturas -->
<div class="card">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Mis Facturas</h5>
    </div>
    <div class="card-body">
        @if($ventas->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>N° Factura</th>
                            <th>Fecha</th>
                            <th>Reserva</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ventas as $venta)
                        <tr>
                            <td>
                                <strong>FACT-{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</strong>
                            </td>
                            <td>{{ $venta->updated_at->format('d/m/Y') }}</td>
                            <td>
                                @if($venta->reserva)
                                    #{{ str_pad($venta->reserva->id, 6, '0', STR_PAD_LEFT) }}<br>
                                    <small>{{ $venta->reserva->habitacion->tipo ?? 'N/A' }}</small>
                                @else
                                    Sin reserva
                                @endif
                            </td>
                            <td>${{ number_format($venta->monto_total, 2) }}</td>
                            <td>
                                @if($venta->estado == 'completada')
                                    <span class="badge bg-success">Pagada</span>
                                @else
                                    <span class="badge bg-warning">{{ ucfirst($venta->estado) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('cliente.facturas.show', $venta->id) }}" 
                                       class="btn btn-info" title="Ver factura">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('cliente.facturas.descargar', $venta->id) }}" 
                                       class="btn btn-success" title="Descargar PDF">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
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
                    <i class="fas fa-file-invoice fa-4x text-muted"></i>
                </div>
                <h4>No tienes facturas</h4>
                <p class="text-muted">Tus facturas aparecerán aquí una vez que sean generadas.</p>
                <a href="{{ route('cliente.reservas.index') }}" class="btn btn-primary">
                    <i class="fas fa-calendar-alt me-2"></i> Ver mis Reservas
                </a>
            </div>
        @endif
    </div>
</div>
@endsection