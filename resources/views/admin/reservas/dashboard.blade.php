@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js">
<style>
    .dashboard-card {
        border-radius: 10px;
        padding: 20px;
        color: white;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .card-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .card-success { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
    .card-warning { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    .card-info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .chart-container {
        position: relative;
        height: 300px;
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">📊 Dashboard de Reservas</h1>
    <div>
        <a href="{{ route('admin.reservas.index') }}" class="btn btn-secondary">
            <i class="fas fa-list"></i> Ver Todas las Reservas
        </a>
    </div>
</div>

<!-- Estadísticas Rápidas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="dashboard-card card-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">{{ $reservasPorEstado->sum('total') }}</h3>
                    <small>Total Reservas</small>
                </div>
                <i class="fas fa-calendar-alt fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card card-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">{{ $reservasPorEstado->where('estado', 'confirmada')->first()->total ?? 0 }}</h3>
                    <small>Confirmadas</small>
                </div>
                <i class="fas fa-check-circle fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card card-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">{{ $reservasPorEstado->where('estado', 'pendiente')->first()->total ?? 0 }}</h3>
                    <small>Pendientes</small>
                </div>
                <i class="fas fa-clock fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-card card-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">{{ $reservasPorEstado->where('estado', 'cancelada')->first()->total ?? 0 }}</h3>
                    <small>Canceladas</small>
                </div>
                <i class="fas fa-times-circle fa-2x"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfico de Reservas por Estado -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Reservas por Estado</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="estadoChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Reservas por Mes -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Reservas por Mes (Últimos 6 meses)</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="mesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Próximas Reservas -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Próximas Reservas (Próximos 7 días)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Habitación</th>
                                <th>Entrada</th>
                                <th>Salida</th>
                                <th>Noches</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($proximasReservas as $reserva)
                            <tr>
                                <td>{{ $reserva->cliente->nombre }} {{ $reserva->cliente->apellido }}</td>
                                <td>{{ $reserva->habitacion->tipo }}</td>
                                <td>{{ $reserva->fecha_entrada->format('d/m/Y') }}</td>
                                <td>{{ $reserva->fecha_salida->format('d/m/Y') }}</td>
                                <td>{{ $reserva->noches }}</td>
                                <td>${{ number_format($reserva->precio_total, 2) }}</td>
                                <td>
                                    <span class="badge {{ $reserva->estado == 'confirmada' ? 'bg-success' : 'bg-warning' }}">
                                        {{ ucfirst($reserva->estado) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.reservas.show', $reserva->id) }}" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No hay reservas próximas</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Habitaciones Más Reservadas -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bed me-2"></i>Habitaciones Más Reservadas</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Habitación</th>
                                <th>Total Reservas</th>
                                <th>Porcentaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($habitacionesPopulares as $habitacion)
                            <tr>
                                <td>{{ $habitacion->tipo }}</td>
                                <td>{{ $habitacion->total_reservas }}</td>
                                <td>
                                    <div class="progress">
                                        @php
                                            $total = $habitacionesPopulares->sum('total_reservas');
                                            $percentage = $total > 0 ? ($habitacion->total_reservas / $total) * 100 : 0;
                                        @endphp
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: {{ $percentage }}%">
                                            {{ round($percentage, 1) }}%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de Reservas por Estado
    const estadoCtx = document.getElementById('estadoChart').getContext('2d');
    const estadoData = {
        labels: ['Confirmadas', 'Pendientes', 'Canceladas'],
        datasets: [{
            data: [
                {{ $reservasPorEstado->where('estado', 'confirmada')->first()->total ?? 0 }},
                {{ $reservasPorEstado->where('estado', 'pendiente')->first()->total ?? 0 }},
                {{ $reservasPorEstado->where('estado', 'cancelada')->first()->total ?? 0 }}
            ],
            backgroundColor: [
                '#43e97b',
                '#fa709a',
                '#4facfe'
            ]
        }]
    };
    
    new Chart(estadoCtx, {
        type: 'doughnut',
        data: estadoData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Gráfico de Reservas por Mes
    const mesCtx = document.getElementById('mesChart').getContext('2d');
    const mesData = {
        labels: {!! json_encode($reservasPorMes->pluck('mes')) !!},
        datasets: [{
            label: 'Número de Reservas',
            data: {!! json_encode($reservasPorMes->pluck('total')) !!},
            backgroundColor: 'rgba(74, 111, 165, 0.2)',
            borderColor: 'rgba(74, 111, 165, 1)',
            borderWidth: 2,
            tension: 0.4
        }]
    };
    
    new Chart(mesCtx, {
        type: 'line',
        data: mesData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endsection