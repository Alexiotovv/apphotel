@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">✏️ Editar Reserva</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.reservas.index') }}">Reservas</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reservas.show', $reserva->id) }}">Reserva #{{ str_pad($reserva->id, 6, '0', STR_PAD_LEFT) }}</a></li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.reservas.show', $reserva->id) }}" class="btn btn-secondary">
                ↩️ Volver a Detalles
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📋 Formulario de Edición</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.reservas.update', $reserva->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">👤 Cliente</label>
                                <div class="alert alert-light">
                                    <strong>{{ $reserva->cliente->nombre }} {{ $reserva->cliente->apellido }}</strong><br>
                                    <small>DNI: {{ $reserva->cliente->dni }} | Tel: {{ $reserva->cliente->telefono }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">📧 Email</label>
                                <input type="email" class="form-control" value="{{ $reserva->cliente->email }}" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">🏨 Habitación *</label>
                                <select name="habitacion_id" class="form-select" required>
                                    <option value="">Seleccionar habitación...</option>
                                    @foreach($habitaciones as $hab)
                                        <option value="{{ $hab->id }}" 
                                            {{ $reserva->habitacion_id == $hab->id ? 'selected' : '' }}
                                            data-precio="{{ $hab->precio_noche }}">
                                            {{ $hab->tipo }} - ${{ number_format($hab->precio_noche, 2) }}/noche
                                            (Capacidad: {{ $hab->capacidad }} personas)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">📅 Fecha Entrada *</label>
                                <input type="date" name="fecha_entrada" class="form-control" 
                                       value="{{ $reserva->fecha_entrada->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">📅 Fecha Salida *</label>
                                <input type="date" name="fecha_salida" class="form-control" 
                                       value="{{ $reserva->fecha_salida->format('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">👨‍👩‍👧‍👦 Adultos *</label>
                                <select name="adultos" class="form-select" required>
                                    @for($i = 1; $i <= 4; $i++)
                                        <option value="{{ $i }}" {{ $reserva->adultos == $i ? 'selected' : '' }}>
                                            {{ $i }} adulto{{ $i > 1 ? 's' : '' }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">🧒 Niños</label>
                                <select name="ninos" class="form-select">
                                    @for($i = 0; $i <= 3; $i++)
                                        <option value="{{ $i }}" {{ $reserva->ninos == $i ? 'selected' : '' }}>
                                            {{ $i }} niño{{ $i > 1 ? 's' : '' }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">💰 Precio Total *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="precio_total" class="form-control" 
                                           step="0.01" min="0" value="{{ $reserva->precio_total }}" required>
                                </div>
                                <small class="text-muted">Precio total de la estadía</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">📊 Estado *</label>
                                <select name="estado" class="form-select" required>
                                    <option value="pendiente" {{ $reserva->estado == 'pendiente' ? 'selected' : '' }}>⏳ Pendiente</option>
                                    <option value="confirmada" {{ $reserva->estado == 'confirmada' ? 'selected' : '' }}>✅ Confirmada</option>
                                    <option value="cancelada" {{ $reserva->estado == 'cancelada' ? 'selected' : '' }}>❌ Cancelada</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">📝 Notas del Administrador</label>
                            <textarea name="notas_admin" class="form-control" rows="3" 
                                      placeholder="Agregar notas internas o comentarios sobre esta reserva...">{{ old('notas_admin', $reserva->notas_admin) }}</textarea>
                            <small class="text-muted">Estas notas solo son visibles para administradores</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">📋 Notas del Cliente (original)</label>
                            <textarea class="form-control" rows="2" readonly>{{ $reserva->notas }}</textarea>
                        </div>

                        <div class="alert alert-info">
                            <h6>📊 Información de la reserva:</h6>
                            <ul class="mb-0">
                                <li>📅 <strong>Fecha de creación:</strong> {{ $reserva->created_at->format('d/m/Y H:i') }}</li>
                                <li>👤 <strong>Creada por:</strong> Sistema web</li>
                                <li>🆔 <strong>ID Reserva:</strong> #{{ str_pad($reserva->id, 6, '0', STR_PAD_LEFT) }}</li>
                                @if($reserva->venta)
                                    <li>💰 <strong>Venta asociada:</strong> V-{{ str_pad($reserva->venta->id, 6, '0', STR_PAD_LEFT) }}</li>
                                @endif
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.reservas.show', $reserva->id) }}" class="btn btn-secondary">
                                ↩️ Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                💾 Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Calculadora de Precios -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">🧮 Calculadora de Precio</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Precio por noche</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" id="precio_noche" class="form-control" value="{{ $reserva->habitacion->precio_noche }}" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Número de noches</label>
                        <input type="number" id="noches" class="form-control" value="{{ $reserva->noches }}" min="1">
                    </div>
                    <div class="alert alert-light">
                        <p class="mb-1">Total calculado:</p>
                        <h3 class="text-success mb-0" id="total_calculado">${{ number_format($reserva->precio_total, 2) }}</h3>
                    </div>
                    <button type="button" class="btn btn-outline-success w-100 mt-2" id="aplicar_calculo">
                        🔄 Aplicar al Total
                    </button>
                </div>
            </div>

            <!-- Resumen Actual -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">📋 Resumen Actual</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Habitación:</small>
                        <p class="mb-0">{{ $reserva->habitacion->tipo }}</p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Fechas:</small>
                        <p class="mb-0">
                            {{ $reserva->fecha_entrada->format('d/m/Y') }} → {{ $reserva->fecha_salida->format('d/m/Y') }}
                        </p>
                        <small>{{ $reserva->noches }} noche{{ $reserva->noches > 1 ? 's' : '' }}</small>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Huéspedes:</small>
                        <p class="mb-0">
                            👤 {{ $reserva->adultos }} adulto{{ $reserva->adultos > 1 ? 's' : '' }}
                            @if($reserva->ninos > 0)
                                <br>🧒 {{ $reserva->ninos }} niño{{ $reserva->ninos > 1 ? 's' : '' }}
                            @endif
                        </p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Estado actual:</small>
                        <p class="mb-0">
                            @if($reserva->estado == 'pendiente')
                                <span class="badge bg-warning">⏳ Pendiente</span>
                            @elseif($reserva->estado == 'confirmada')
                                <span class="badge bg-success">✅ Confirmada</span>
                            @else
                                <span class="badge bg-danger">❌ Cancelada</span>
                            @endif
                        </p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Precio actual:</small>
                        <h5 class="text-primary mb-0">${{ number_format($reserva->precio_total, 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-label {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .alert-light {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    .badge {
        padding: 5px 10px;
        border-radius: 5px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const habitacionSelect = document.querySelector('select[name="habitacion_id"]');
        const precioNocheInput = document.getElementById('precio_noche');
        const fechaEntradaInput = document.querySelector('input[name="fecha_entrada"]');
        const fechaSalidaInput = document.querySelector('input[name="fecha_salida"]');
        const nochesInput = document.getElementById('noches');
        const totalCalculadoSpan = document.getElementById('total_calculado');
        const precioTotalInput = document.querySelector('input[name="precio_total"]');
        const aplicarBtn = document.getElementById('aplicar_calculo');

        // Actualizar precio por noche cuando cambia la habitación
        habitacionSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const precio = selectedOption.getAttribute('data-precio') || 0;
            precioNocheInput.value = precio;
            calcularTotal();
        });

        // Calcular noches cuando cambian las fechas
        function calcularNoches() {
            const entrada = new Date(fechaEntradaInput.value);
            const salida = new Date(fechaSalidaInput.value);
            
            if (entrada && salida && salida > entrada) {
                const diffTime = Math.abs(salida - entrada);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                nochesInput.value = diffDays;
                calcularTotal();
            } else {
                nochesInput.value = 0;
                totalCalculadoSpan.textContent = '$0.00';
            }
        }

        fechaEntradaInput.addEventListener('change', calcularNoches);
        fechaSalidaInput.addEventListener('change', calcularNoches);

        // Calcular total cuando cambian noches
        nochesInput.addEventListener('input', calcularTotal);

        function calcularTotal() {
            const precioNoche = parseFloat(precioNocheInput.value) || 0;
            const noches = parseInt(nochesInput.value) || 0;
            const total = precioNoche * noches;
            
            totalCalculadoSpan.textContent = '$' + total.toFixed(2);
        }

        // Aplicar cálculo al campo de precio total
        aplicarBtn.addEventListener('click', function() {
            const totalCalculado = parseFloat(totalCalculadoSpan.textContent.replace('$', ''));
            if (totalCalculado > 0) {
                precioTotalInput.value = totalCalculado.toFixed(2);
                Swal.fire({
                    icon: 'success',
                    title: 'Precio actualizado',
                    text: 'El precio total ha sido calculado automáticamente',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });

        // Inicializar cálculo
        calcularNoches();
    });
</script>
@endsection