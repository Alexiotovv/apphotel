@extends('layouts.cliente')

@section('title', 'Detalles de Factura')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-file-invoice me-2"></i>Detalles de Factura
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('cliente.facturas.index') }}">Facturas</a></li>
                    <li class="breadcrumb-item active">FACT-{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('cliente.facturas.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <!-- Tarjeta Principal -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            FACTURA FACT-{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}
                        </h5>
                        <span class="badge bg-success fs-6">Pagada</span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Información de la Factura -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="border p-3 rounded bg-light">
                                <h6 class="text-muted mb-3">INFORMACIÓN DE FACTURA</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <p class="mb-1"><strong>N° Factura:</strong></p>
                                        <p class="mb-3">FACT-{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1"><strong>Fecha Emisión:</strong></p>
                                        <p class="mb-3">{{ $venta->updated_at->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1"><strong>Venta ID:</strong></p>
                                        <p class="mb-3">#{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1"><strong>Reserva ID:</strong></p>
                                        <p class="mb-3">
                                            @if($venta->reserva)
                                                #{{ str_pad($venta->reserva->id, 6, '0', STR_PAD_LEFT) }}
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="border p-3 rounded bg-light">
                                <h6 class="text-muted mb-3">ESTADO</h6>
                                <div class="text-center py-2">
                                    @if($venta->estado == 'completada')
                                        <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                                        <h4 class="text-success">FACTURA PAGADA</h4>
                                        <p class="mb-0">Fecha de pago: {{ $venta->updated_at->format('d/m/Y') }}</p>
                                    @else
                                        <i class="fas fa-clock fa-3x text-warning mb-2"></i>
                                        <h4 class="text-warning">PENDIENTE DE PAGO</h4>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Cliente -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>INFORMACIÓN DEL CLIENTE</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Nombre:</strong></p>
                                    <p>{{ $venta->reserva->cliente->nombre ?? 'N/A' }} {{ $venta->reserva->cliente->apellido ?? '' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>DNI:</strong></p>
                                    <p>{{ $venta->reserva->cliente->dni ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Email:</strong></p>
                                    <p>{{ $venta->reserva->cliente->email ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Teléfono:</strong></p>
                                    <p>{{ $venta->reserva->cliente->telefono ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-12">
                                    <p class="mb-1"><strong>Dirección:</strong></p>
                                    <p>{{ $venta->reserva->cliente->direccion ?? 'No especificada' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles de la Reserva -->
                    @if($venta->reserva)
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>DETALLES DE RESERVA</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>Habitación:</strong></p>
                                    <p>{{ $venta->reserva->habitacion->tipo ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>Check-in:</strong></p>
                                    <p>{{ \Carbon\Carbon::parse($venta->reserva->fecha_entrada)->format('d/m/Y') }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>Check-out:</strong></p>
                                    <p>{{ \Carbon\Carbon::parse($venta->reserva->fecha_salida)->format('d/m/Y') }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>Noches:</strong></p>
                                    <p>{{ $venta->reserva->noches }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>Adultos:</strong></p>
                                    <p>{{ $venta->reserva->adultos }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>Niños:</strong></p>
                                    <p>{{ $venta->reserva->ninos }}</p>
                                </div>
                            </div>
                            @if($venta->reserva->notas)
                            <div class="row mt-3">
                                <div class="col-12">
                                    <p class="mb-1"><strong>Notas:</strong></p>
                                    <p class="bg-light p-2 rounded">{{ $venta->reserva->notas }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Detalles de Pago -->
                    @if($venta->pagos && $venta->pagos->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>DETALLES DE PAGO</h6>
                        </div>
                        <div class="card-body">
                            @foreach($venta->pagos as $pago)
                                @if($pago->estado == 'completado')
                                <div class="row border-bottom pb-3 mb-3">
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Método:</strong></p>
                                        <p>
                                            @if($pago->metodo_pago == 'tarjeta')
                                                <i class="fas fa-credit-card me-1"></i> Tarjeta
                                            @elseif($pago->metodo_pago == 'qr')
                                                <i class="fas fa-qrcode me-1"></i> QR
                                            @else
                                                {{ ucfirst($pago->metodo_pago) }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Referencia:</strong></p>
                                        <p>{{ $pago->referencia }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Monto:</strong></p>
                                        <p class="h5 text-success">${{ number_format($pago->monto, 2) }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Fecha:</strong></p>
                                        <p>{{ $pago->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Estado:</strong></p>
                                        <span class="badge bg-success">Completado</span>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Botones de Acción -->
                    <div class="d-flex justify-content-between">
                        <div class="btn-group">
                            <a href="{{ route('cliente.facturas.descargar', $venta->id) }}" 
                               class="btn btn-success" target="_blank">
                                <i class="fas fa-download me-1"></i> Descargar PDF
                            </a>
                            <a href="{{ route('cliente.facturas.descargar', ['id' => $venta->id, 'preview' => 1]) }}" 
                               class="btn btn-info" target="_blank">
                                <i class="fas fa-eye me-1"></i> Previsualizar
                            </a>
                        </div>
                        
                        <!-- Botón para reenviar factura -->
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reenviarModal">
                            <i class="fas fa-paper-plane me-1"></i> Reenviar Factura
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Lateral - Resumen -->
        <div class="col-lg-4">
            <!-- Resumen de Montos -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>RESUMEN DE FACTURA</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>Subtotal:</span>
                        <strong>${{ number_format($venta->monto_total, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>IVA (19%):</span>
                        <strong>${{ number_format($venta->monto_total * 0.19, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Descuentos:</span>
                        <strong>$0.00</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="h5">TOTAL:</span>
                        <span class="h4 text-primary">${{ number_format($venta->monto_total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Datos de Facturación -->
            @if($datosFacturacion)
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>DATOS DE FACTURACIÓN</h5>
                </div>
                <div class="card-body">
                    @if(isset($datosFacturacion['razon_social']))
                    <p class="mb-1"><strong>Razón Social:</strong></p>
                    <p>{{ $datosFacturacion['razon_social'] }}</p>
                    @endif
                    
                    @if(isset($datosFacturacion['rut']))
                    <p class="mb-1"><strong>RUT:</strong></p>
                    <p>{{ $datosFacturacion['rut'] }}</p>
                    @endif
                    
                    @if(isset($datosFacturacion['direccion_fiscal']))
                    <p class="mb-1"><strong>Dirección Fiscal:</strong></p>
                    <p>{{ $datosFacturacion['direccion_fiscal'] }}</p>
                    @endif
                    
                    @if(isset($datosFacturacion['giro']))
                    <p class="mb-1"><strong>Giro:</strong></p>
                    <p>{{ $datosFacturacion['giro'] }}</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Información de Contacto -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>INFORMACIÓN DE CONTACTO</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        <strong>Email:</strong> facturacion@hotelici.com
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-phone me-2"></i>
                        <strong>Teléfono:</strong> +56 9 1234 5678
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Horario:</strong> Lunes a Viernes 9:00 - 18:00
                    </p>
                    <hr>
                    <p class="text-muted small">
                        Si tienes alguna duda sobre esta factura, por favor contacta a nuestro departamento de facturación.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para reenviar factura -->
<div class="modal fade" id="reenviarModal" tabindex="-1" aria-labelledby="reenviarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="reenviarModalLabel">
                    <i class="fas fa-paper-plane me-2"></i>Reenviar Factura por Email
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('cliente.facturas.reenviar', $venta->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Ingresa el email al que deseas reenviar la factura:</p>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Destino *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="{{ $venta->reserva->cliente->email ?? '' }}" required>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        La factura será enviada en formato PDF adjunto al email especificado.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> Enviar Factura
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
    .border-bottom {
        border-bottom: 1px solid #dee2e6 !important;
    }
    .bg-light {
        background-color: #f8f9fa !important;
    }
</style>
@endpush