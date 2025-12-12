@extends('layouts.cliente')

@section('title', 'Proceder al Pago')

@section('css')
<style>
    .payment-method {
        border: 2px solid #dee2e6;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .payment-method:hover {
        border-color: #4a6fa5;
        background: #f8f9fa;
    }
    .payment-method.selected {
        border-color: #4a6fa5;
        background: #e3f2fd;
    }
    .card-icons img {
        height: 30px;
        margin-right: 10px;
    }
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">
                    <i class="fas fa-credit-card me-2"></i>Proceder al Pago
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('cliente.reservas.index') }}">Mis Reservas</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('cliente.reservas.show', $reserva->id) }}">Reserva #{{ str_pad($reserva->id, 6, '0', STR_PAD_LEFT) }}</a></li>
                        <li class="breadcrumb-item active">Pagar</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('cliente.reservas.show', $reserva->id) }}" class="btn btn-secondary">
                ↩️ Volver
            </a>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Detalles de la Compra</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>📋 Reserva</h6>
                        <p class="mb-1"><strong>Número:</strong> #{{ str_pad($reserva->id, 6, '0', STR_PAD_LEFT) }}</p>
                        <p class="mb-1"><strong>Habitación:</strong> {{ $reserva->habitacion->tipo }}</p>
                        <p class="mb-0"><strong>Noches:</strong> {{ $reserva->noches }}</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h6>💰 Total a Pagar</h6>
                        <h2 class="text-success">${{ number_format($reserva->precio_total, 2) }}</h2>
                    </div>
                </div>

                <!-- Métodos de Pago -->
                <h5 class="mb-3"><i class="fas fa-wallet me-2"></i>Selecciona Método de Pago</h5>
                
                <div class="row mb-4">
                    <!-- Tarjeta de Crédito/Débito -->
                    <div class="col-md-6">
                        <div class="payment-method" id="tarjetaMethod">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="metodo_pago" 
                                       id="tarjeta" value="tarjeta" checked>
                                <label class="form-check-label" for="tarjeta">
                                    <h6><i class="fas fa-credit-card me-2"></i>Tarjeta de Crédito/Débito</h6>
                                </label>
                            </div>
                            <div class="card-icons mt-2">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b7/Visa_Logo.svg/1200px-Visa_Logo.svg.png" 
                                     alt="Visa">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/1200px-Mastercard-logo.svg.png" 
                                     alt="Mastercard">
                            </div>
                        </div>
                    </div>

                    <!-- Código QR -->
                    <div class="col-md-6">
                        <div class="payment-method" id="qrMethod">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="metodo_pago" 
                                       id="qr" value="qr">
                                <label class="form-check-label" for="qr">
                                    <h6><i class="fas fa-qrcode me-2"></i>Código QR</h6>
                                </label>
                            </div>
                            <div class="mt-2 text-center">
                                <i class="fas fa-mobile-alt fa-2x text-muted"></i>
                                <p class="small mb-0">Escanea con tu app bancaria</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Tarjeta -->
                <div id="tarjetaForm">
                    <hr>
                    <h5 class="mb-3"><i class="fas fa-credit-card me-2"></i>Información de la Tarjeta</h5>
                    
                    <form id="tarjetaPaymentForm" action="{{ route('cliente.reservas.pagar.tarjeta', $reserva->id) }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Número de Tarjeta *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
                                    <input type="text" name="numero_tarjeta" id="numero_tarjeta" class="form-control" 
                                           placeholder="1234 5678 9012 3456" required 
                                           maxlength="19">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre del Titular *</label>
                                <input type="text" name="nombre_titular" class="form-control" 
                                       placeholder="Como aparece en la tarjeta" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Mes de Expiración *</label>
                                <select name="mes_expiracion" class="form-select" required>
                                    <option value="">Mes</option>
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                                            {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Año de Expiración *</label>
                                <select name="ano_expiracion" class="form-select" required>
                                    <option value="">Año</option>
                                    @for($i = date('Y'); $i <= date('Y') + 10; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">CVV *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="text" name="cvv" class="form-control" 
                                           placeholder="123" required maxlength="4">
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-shield-alt"></i> 
                            <strong>Pago seguro:</strong> Todos los datos se transmiten mediante encriptación SSL.
                            <br><small class="text-muted">Para propósitos de demostración, puedes usar: 4111 1111 1111 1111 (Visa test)</small>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                💳 Pagar ${{ number_format($reserva->precio_total, 2) }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Formulario QR -->
                <div id="qrForm" style="display: none;">
                    <hr>
                    <h5 class="mb-3"><i class="fas fa-qrcode me-2"></i>Pago con Código QR</h5>
                    
                    <div class="text-center">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Instrucciones:</strong><br>
                            1. Selecciona "Generar QR"<br>
                            2. Escanea el código con tu app bancaria<br>
                            3. Realiza el pago desde tu banco<br>
                            4. Espera la confirmación
                        </div>
                        
                        <form id="qrPaymentForm" action="{{ route('cliente.reservas.pagar.qr', $reserva->id) }}" method="POST">
                            @csrf
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    📱 Generar Código QR
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información Adicional -->
        <div class="alert alert-light mt-4">
            <h6><i class="fas fa-question-circle me-2"></i> ¿Necesitas ayuda?</h6>
            <p class="mb-0">
                Si tienes problemas con el pago, contacta a nuestro soporte:<br>
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
        // Elementos DOM
        const tarjetaMethod = document.getElementById('tarjetaMethod');
        const qrMethod = document.getElementById('qrMethod');
        const tarjetaForm = document.getElementById('tarjetaForm');
        const qrForm = document.getElementById('qrForm');
        const tarjetaRadio = document.getElementById('tarjeta');
        const qrRadio = document.getElementById('qr');
        const numeroTarjetaInput = document.getElementById('numero_tarjeta');
        
        // Función para formatear número de tarjeta
        function formatearNumeroTarjeta(input) {
            let value = input.value.replace(/\D/g, '');
            let formatted = '';
            
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formatted += ' ';
                }
                formatted += value[i];
            }
            
            input.value = formatted.substring(0, 19);
        }
        
        // Evento para formatear número de tarjeta
        if (numeroTarjetaInput) {
            numeroTarjetaInput.addEventListener('input', function() {
                formatearNumeroTarjeta(this);
            });
            
            // También formatear al cargar si ya hay valor
            if (numeroTarjetaInput.value) {
                formatearNumeroTarjeta(numeroTarjetaInput);
            }
        }
        
        // Función para actualizar formularios según método seleccionado
        function updatePaymentForms() {
            if (tarjetaRadio.checked) {
                // Mostrar formulario de tarjeta
                tarjetaMethod.classList.add('selected');
                qrMethod.classList.remove('selected');
                tarjetaForm.style.display = 'block';
                qrForm.style.display = 'none';
            } else {
                // Mostrar formulario de QR
                tarjetaMethod.classList.remove('selected');
                qrMethod.classList.add('selected');
                tarjetaForm.style.display = 'none';
                qrForm.style.display = 'block';
            }
        }
        
        // Eventos para selección de método de pago
        if (tarjetaMethod) {
            tarjetaMethod.addEventListener('click', function() {
                tarjetaRadio.checked = true;
                updatePaymentForms();
            });
        }
        
        if (qrMethod) {
            qrMethod.addEventListener('click', function() {
                qrRadio.checked = true;
                updatePaymentForms();
            });
        }
        
        if (tarjetaRadio) {
            tarjetaRadio.addEventListener('change', updatePaymentForms);
        }
        
        if (qrRadio) {
            qrRadio.addEventListener('change', updatePaymentForms);
        }
        
        // Validar formulario de tarjeta antes de enviar
        const tarjetaFormEl = document.getElementById('tarjetaPaymentForm');
        if (tarjetaFormEl) {
            tarjetaFormEl.addEventListener('submit', function(e) {
                // Validar CVV
                const cvv = this.querySelector('input[name="cvv"]').value;
                if (!/^\d{3,4}$/.test(cvv)) {
                    e.preventDefault();
                    alert('El CVV debe tener 3 o 4 dígitos numéricos.');
                    return false;
                }
                
                // Validar número de tarjeta (mínimo 16 dígitos)
                const tarjeta = this.querySelector('input[name="numero_tarjeta"]').value.replace(/\D/g, '');
                if (tarjeta.length < 16) {
                    e.preventDefault();
                    alert('El número de tarjeta debe tener al menos 16 dígitos.');
                    return false;
                }
                
                // Mostrar estado de carga
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Procesando pago...';
                submitBtn.disabled = true;
                
                // El formulario se enviará normalmente
            });
        }
        
        // Inicializar
        updatePaymentForms();
    });
</script>
@endsection