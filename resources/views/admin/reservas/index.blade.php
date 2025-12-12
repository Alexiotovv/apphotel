@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .estado-badge {
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
    }
    .estado-pendiente { background: #fff3cd; color: #856404; }
    .estado-confirmada { background: #d4edda; color: #155724; }
    .estado-cancelada { background: #f8d7da; color: #721c24; }
    .stats-card {
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        color: white;
        margin-bottom: 15px;
    }
    .stats-total { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .stats-pendiente { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .stats-confirmada { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stats-cancelada { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .stats-ingresos { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">📋 Gestión de Reservas</h1>
    <div>
        <a href="{{ route('admin.reservas.dashboard') }}" class="btn btn-outline-primary me-2">
            <i class="fas fa-chart-bar"></i> Dashboard
        </a>
        <a href="{{ route('admin.reservas.exportar') }}" class="btn btn-success">
            <i class="fas fa-file-export"></i> Exportar
        </a>
    </div>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="stats-card stats-total">
            <h4 class="mb-1">{{ $stats['total'] }}</h4>
            <small>Total Reservas</small>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stats-card stats-pendiente">
            <h4 class="mb-1">{{ $stats['pendientes'] }}</h4>
            <small>Pendientes</small>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stats-card stats-confirmada">
            <h4 class="mb-1">{{ $stats['confirmadas'] }}</h4>
            <small>Confirmadas</small>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stats-card stats-cancelada">
            <h4 class="mb-1">{{ $stats['canceladas'] }}</h4>
            <small>Canceladas</small>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stats-card stats-ingresos">
            <h4 class="mb-1">${{ number_format($stats['ingresos_hoy'], 0) }}</h4>
            <small>Ingresos Hoy</small>
        </div>
    </div>
    <div class="col-md-2">
        <div class="stats-card stats-ingresos">
            <h4 class="mb-1">${{ number_format($stats['ingresos_mes'], 0) }}</h4>
            <small>Ingresos Mes</small>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reservas.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="confirmada" {{ request('estado') == 'confirmada' ? 'selected' : '' }}>Confirmada</option>
                    <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Cliente (Nombre/DNI)</label>
                <input type="text" name="cliente" class="form-control" value="{{ request('cliente') }}" placeholder="Buscar cliente...">
            </div>
            <div class="col-12">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.reservas.index') }}" class="btn btn-secondary me-2">Limpiar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Reservas -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="reservasTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Contacto</th>
                        <th>Habitación</th>
                        <th>Fechas</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Fecha Reserva</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservas as $reserva)
                    <tr>
                        <td>#{{ str_pad($reserva->id, 6, '0', STR_PAD_LEFT) }}</td>
                        <td>
                            <strong>{{ $reserva->cliente->nombre }} {{ $reserva->cliente->apellido }}</strong><br>
                            <small class="text-muted">DNI: {{ $reserva->cliente->dni }}</small>
                        </td>
                        <td>
                            <small>{{ $reserva->cliente->email }}</small><br>
                            <small>{{ $reserva->cliente->telefono }}</small>
                        </td>
                        <td>{{ $reserva->habitacion->tipo }}</td>
                        <td>
                            <small>Entrada: {{ $reserva->fecha_entrada->format('d/m/Y') }}</small><br>
                            <small>Salida: {{ $reserva->fecha_salida->format('d/m/Y') }}</small><br>
                            <small>{{ $reserva->noches }} noches</small>
                        </td>
                        <td>${{ number_format($reserva->precio_total, 2) }}</td>
                        <td>
                            <span class="estado-badge estado-{{ $reserva->estado }}">
                                {{ ucfirst($reserva->estado) }}
                            </span>
                        </td>
                        <td>{{ $reserva->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.reservas.show', $reserva->id) }}" 
                                class="btn btn-info" title="Ver detalles">
                                    👁️
                                </a>
                                <a href="{{ route('admin.reservas.edit', $reserva->id) }}" 
                                class="btn btn-warning" title="Editar">
                                    ✏️
                                </a>
                                @if($reserva->estado == 'pendiente')
                                    <form action="{{ route('admin.reservas.confirmar', $reserva->id) }}" 
                                        method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success" title="Confirmar">
                                            ✅
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.reservas.cancelar', $reserva->id) }}" 
                                        method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" title="Cancelar"
                                                onclick="return confirm('¿Seguro que deseas cancelar esta reserva?')">
                                            ❌
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.reservas.destroy', $reserva->id) }}" 
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('¿Eliminar definitivamente esta reserva?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-dark" title="Eliminar">
                                        🗑️
                                    </button>
                                </form>
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
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#reservasTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            pageLength: 25,
            order: [[0, 'desc']],
            dom: '<"top"f>rt<"bottom"ip><"clear">'
        });
    });
</script>
@endsection