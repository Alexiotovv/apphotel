@extends('layouts.cliente')

@section('title', 'Comprobante de Pago')

@section('css')
<style>
    .receipt {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .receipt-header {
        border-bottom: 3px solid #4a6fa5;
        padding-bottom: 20px;
        margin-bottom: 20px;
        text-align: center;
    }
    .receipt-body {
        margin-bottom: 30px;
    }
    .receipt-footer {
        border-top: 1px solid #dee2e6;
        padding-top: 20px;
        text-align: center;
        color: #666;
    }
    .no-print {
        display: block;
    }
    @media print {
        .no-print {
            display: none !important;
        }
        .receipt {
            border: none;
            box-shadow: none;
            width: 100%;
        }
        body {
            background: white !important;
        }
    }
    .status-badge {
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .status-completado {
        background: #d4edda;
        color: #155724;
    }
    .status-pendiente {
        background: #fff3cd;
        color: #856404;
    }
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h1 class="h3 mb-0">
                    <i class="fas fa-receipt me-2"></i>Comprobante de Pago
                </h1>
                <p class="text-muted mb-0">Pago realizado exitosamente</p>
            </div>
            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print me-1"></i> Imprimir
                </button>
                <a href="{{ route('cliente.reservas.show', $pago->venta->reserva->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver a Reserva
                </a>
            </div>
        </div>

        <!-- Comprobante -->
        <div class="receipt">
            <!-- Encabezado -->
            <div class="receipt-header">
                <h2 class="mb-2">🏨 Hotel ICI</h2>
                <p class="mb-1">Av. Siempre Viva 123, Ciudad</p>
                <p class="mb-1">📞 +56 9 1234 5678</p>
                <p class="mb-0">✉️ contacto@hotelici.com</p>
            </div>

            <!-- Título -->
            <div class="text-center mb-4">
                <h3 class="text-uppercase mb-1">Comprobante de Pago</h3>
                <p class="text-muted mb-0">N° {{ $pago->referencia }}</p>
                <p class="text-muted">
                    <small>Fecha: {{ $pago->created_at->format('d/m/Y H:i:s') }}</small>
                </p>
            </div>

            <!-- Información del Pago -->
            <div class="receipt-body">
                <!-- Estado del Pago -->
                <div class="text-center mb-4">
                    <span class="status-badge status-completado">
                        ✅ PAGO COMPLETADO
                    </span>
                </div>

                <!-- Reserva -->
                <div class="alert alert-light mb-4">
                    <h6 class="mb-2"><i class="fas fa-calendar-alt me-2"></i>Información de la Reserva</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Número:</strong></p>
                            <p>#{{ str_pad($pago->venta->reserva->id, 6, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Habitación:</strong></p>
                            <p>{{ $pago->venta->reserva->habitacion->tipo }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Cliente:</strong></p>
                            <p>{{ $pago->venta->reserva->cliente->nombre }} {{ $pago->venta->reserva->cliente->apellido }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>DNI:</strong></p>
                            <p>{{ $pago->venta->reserva->cliente->dni }}</p>
                        </div>
                    </div>
                </div>

                <!-- Monto -->
                <div class="text-center py-3 border rounded bg-light mb-4">
                    <p class="mb-1">Total Pagado</p>
                    <h1 class="text-success mb-0">${{ number_format($pago->monto, 2) }}</h1>
                    <p class="mb-0 text-muted">
                        {{ $pago->venta->reserva->noches }} noches × 
                        ${{ number_format($pago->venta->reserva->habitacion->precio_noche, 2) }}
                    </p>
                </div>

                <!-- Detalles del Pago -->
                <div class="mb-4">
                    <h6><i class="fas fa-credit-card me-2"></i>Detalles del Pago</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Método:</strong></p>
                            <p>
                                @if($pago->metodo_pago == 'tarjeta')
                                    <i class="fas fa-credit-card me-1"></i> Tarjeta de Crédito/Débito
                                @elseif($pago->metodo_pago == 'qr')
                                    <i class="fas fa-qrcode me-1"></i> Código QR
                                @else
                                    {{ ucfirst($pago->metodo_pago) }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Referencia:</strong></p>
                            <p><code>{{ $pago->referencia }}</code></p>
                        </div>
                    </div>
                    
                    @if($pago->metodo_pago == 'tarjeta' && $pago->detalles)
                        @php
                            $detalles = json_decode($pago->detalles, true);
                        @endphp
                        <p class="mb-1"><strong>Tarjeta terminada en:</strong></p>
                        <p>**** **** **** {{ $detalles['tarjeta_ultimos_4'] ?? '****' }}</p>
                        
                        @if(isset($detalles['nombre_titular']))
                            <p class="mb-1"><strong>Titular:</strong></p>
                            <p>{{ $detalles['nombre_titular'] }}</p>
                        @endif
                    @endif
                </div>

                <!-- Información de la Venta -->
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Información Adicional</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>ID Venta:</strong></p>
                            <p>V-{{ str_pad($pago->venta->id, 6, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Estado Venta:</strong></p>
                            <p>
                                @if($pago->venta->estado == 'completada')
                                    <span class="badge bg-success">Completada</span>
                                @else
                                    <span class="badge bg-warning">{{ ucfirst($pago->venta->estado) }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pie de Comprobante -->
            <div class="receipt-footer">
                <p class="mb-2">
                    <strong>¡Gracias por tu compra!</strong>
                </p>
                <p class="text-muted mb-0">
                    <small>
                        Este comprobante es válido como constancia de pago.<br>
                        Para cualquier consulta, contacte al hotel.
                    </small>
                </p>
            </div>
        </div>

        <!-- Botones adicionales -->
        <div class="text-center mt-4 no-print">
            <div class="btn-group">
                <a href="{{ route('cliente.reservas.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-calendar-alt me-1"></i> Ver Todas mis Reservas
                </a>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-home me-1"></i> Volver al Inicio
                </a>
            </div>
        </div>

        <!-- Información de Contacto -->
        <div class="alert alert-light mt-4 no-print">
            <h6><i class="fas fa-question-circle me-2"></i> ¿Necesitas ayuda?</h6>
            <p class="mb-0">
                Para consultas sobre este pago, contacta a nuestro servicio al cliente:<br>
                📞 <strong>+56 9 1234 5678</strong> | 
                ✉️ <strong>soporte@hotelici.com</strong>
            </p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configurar impresión
        const printBtn = document.querySelector('button[onclick="window.print()"]');
        if (printBtn) {
            printBtn.addEventListener('click', function() {
                // Esperar un momento antes de imprimir para que carguen los estilos
                setTimeout(() => {
                    window.print();
                }, 100);
            });
        }
        
        // Auto-imprimir si se pasa parámetro ?print=1
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('print') === '1') {
            setTimeout(() => {
                window.print();
            }, 500);
        }
    });
</script>
@endsection