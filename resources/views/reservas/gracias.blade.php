<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Reserva Confirmada! - Hotel ICI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        .hero-confirmation {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://placehold.co/1920x400/20c997/white?text=Reserva+Confirmada') no-repeat center center/cover;
            height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            margin-bottom: 30px;
        }
        .confirmation-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
            border: none;
        }
        .confirmation-header {
            background: linear-gradient(to right, #28a745, #20c997);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .check-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounce 1s;
            color: white;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-20px);}
            60% {transform: translateY(-10px);}
        }
        .reserva-details {
            border-left: 4px solid #20c997;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .badge-date {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        .section-title {
            position: relative;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
        }
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: #4a6fa5;
        }
        .info-box {
            background: linear-gradient(to right, #e3f2fd, #f3e5f5);
            border-left: 4px solid #4a6fa5;
            border-radius: 8px;
            padding: 20px;
        }
        .btn-success {
            background: #28a745;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        .btn-outline-primary {
            border: 2px solid #4a6fa5;
            color: #4a6fa5;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-outline-primary:hover {
            background: #4a6fa5;
            color: white;
        }
        .reserva-number {
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="fas fa-hotel me-2"></i>Hotel ICI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}#inicio">
                            <i class="fas fa-home me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}#habitaciones">
                            <i class="fas fa-bed me-1"></i>Habitaciones
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}#servicios">
                            <i class="fas fa-concierge-bell me-1"></i>Servicios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}#contacto">
                            <i class="fas fa-phone me-1"></i>Contacto
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="{{ route('login') }}" class="nav-link">
                            <i class="fas fa-lock me-1"></i>Acceso Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Banner de Confirmación -->
    <section class="hero-confirmation">
        <div class="container">
            <h1 class="display-5 fw-bold">
                <i class="fas fa-check-circle me-3"></i>¡Reserva Confirmada!
            </h1>
            <p class="lead">Gracias por elegir Hotel ICI</p>
        </div>
    </section>

    <!-- Contenido Principal -->
    <div class="container">
        <div class="confirmation-card">
            @if(isset($reserva))
                <!-- Encabezado de Confirmación -->
                <div class="confirmation-header">
                    <div class="check-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="fw-bold">¡Hola, {{ $reserva->cliente->nombre }}!</h3>
                    <p class="mb-0">Tu reserva ha sido registrada exitosamente.</p>
                </div>

                <div class="p-4 p-md-5">
                    <!-- Número de Reserva -->
                    <div class="text-center mb-5">
                        <h4 class="section-title text-center">Tu Número de Reserva</h4>
                        <div class="reserva-number d-inline-block">
                            <h2 class="mb-0">
                                <i class="fas fa-hashtag text-primary"></i>
                                #{{ str_pad($reserva->id, 6, '0', STR_PAD_LEFT) }}
                            </h2>
                        </div>
                        <p class="text-muted mt-2">
                            <i class="fas fa-calendar-day"></i>
                            Reservado el {{ $reserva->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>

                    <!-- Detalles de la Reserva -->
                    <h4 class="section-title">
                        <i class="fas fa-receipt me-2"></i>Detalles de tu Reserva
                    </h4>
                    
                    <div class="reserva-details p-4 mb-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6><i class="fas fa-user me-2"></i>Información del Cliente</h6>
                                <div class="ps-3">
                                    <p class="mb-1"><strong>Nombre:</strong> {{ $reserva->cliente->nombre }} {{ $reserva->cliente->apellido }}</p>
                                    <p class="mb-1"><strong>DNI:</strong> {{ $reserva->cliente->dni }}</p>
                                    <p class="mb-1"><strong>Email:</strong> {{ $reserva->cliente->email }}</p>
                                    <p class="mb-0"><strong>Teléfono:</strong> {{ $reserva->cliente->telefono }}</p>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <h6><i class="fas fa-bed me-2"></i>Detalles de la Estadía</h6>
                                <div class="ps-3">
                                    <p class="mb-1"><strong>Habitación:</strong> {{ $reserva->habitacion->tipo ?? 'No especificada' }}</p>
                                    <p class="mb-1">
                                        <strong>Fechas:</strong><br>
                                        <span class="badge bg-primary badge-date">
                                            <i class="fas fa-sign-in-alt"></i> Entrada: {{ \Carbon\Carbon::parse($reserva->fecha_entrada)->format('d/m/Y') }}
                                        </span>
                                        <br class="d-md-none">
                                        <span class="badge bg-secondary badge-date mt-1 mt-md-0">
                                            <i class="fas fa-sign-out-alt"></i> Salida: {{ \Carbon\Carbon::parse($reserva->fecha_salida)->format('d/m/Y') }}
                                        </span>
                                    </p>
                                    <p class="mb-0"><strong>Noches:</strong> <span class="h5">{{ $reserva->noches }}</span> noches</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="p-3 bg-white rounded border">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <p class="mb-0"><strong><i class="fas fa-money-bill-wave me-1"></i> Total a pagar:</strong></p>
                                            <small class="text-muted">Incluye impuestos y cargos</small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <h3 class="text-success mb-0">
                                                ${{ number_format($reserva->precio_total, 2) }}
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información Importante -->
                    <div class="info-box mb-4">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-info-circle me-2"></i>Información importante:
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="mb-3 mb-md-0">
                                    <li>Presenta esta confirmación al check-in</li>
                                    <li>Check-in: 15:00 hs | Check-out: 11:00 hs</li>
                                    <li>Se requiere DNI original al ingresar</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="mb-0">
                                    <li>Para modificaciones o cancelaciones:</li>
                                    <li><strong>Email:</strong> contacto@hotelici.com</li>
                                    <li><strong>Teléfono:</strong> +56 9 1234 5678</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="{{ url('/') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-home me-2"></i>Volver al Inicio
                            </a>
                        </div>
                        <div class="col-md-4">
                            <button onclick="window.print()" class="btn btn-success w-100">
                                <i class="fas fa-print me-2"></i>Imprimir
                            </button>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('reservas.create') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-plus me-2"></i>Nueva Reserva
                            </a>
                        </div>
                    </div>

                    <!-- Información Adicional -->
                    <div class="text-center mt-5 pt-3 border-top">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            Tus datos están protegidos según la ley de protección de datos
                        </small>
                        <p class="mt-2 mb-0">
                            <i class="fas fa-phone-alt me-1"></i> +56 9 1234 5678 | 
                            <i class="fas fa-envelope me-1"></i> contacto@hotelici.com
                        </p>
                        <p class="text-muted">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            Hotel ICI - Av. Siempre Viva 123, Ciudad
                        </p>
                    </div>
                </div>

            @else
                <!-- Mensaje de Error -->
                <div class="p-5 text-center">
                    <div class="alert alert-warning border-0 rounded-3 shadow-sm">
                        <div class="mb-3">
                            <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
                        </div>
                        <h4 class="alert-heading">No se encontró la reserva</h4>
                        <p>La reserva solicitada no existe o ha sido eliminada.</p>
                        <div class="mt-3">
                            <a href="{{ route('reservas.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Crear Nueva Reserva
                            </a>
                            <a href="{{ url('/') }}" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-home me-2"></i>Volver al Inicio
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-1">&copy; 2025 Hotel ICI. Todos los derechos reservados.</p>
            <p class="mb-0">Diseñado para el Trabajo Final de Curso - ICI</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animación del icono de check
        document.addEventListener('DOMContentLoaded', function() {
            const checkIcon = document.querySelector('.check-icon');
            checkIcon.style.animation = 'bounce 1s';
            
            // Copiar número de reserva al portapapeles
            const copyBtn = document.getElementById('copyReservaBtn');
            if(copyBtn) {
                copyBtn.addEventListener('click', function() {
                    const reservaNum = document.querySelector('.reserva-number h2').textContent;
                    navigator.clipboard.writeText(reservaNum).then(() => {
                        // Mostrar mensaje temporal
                        const originalText = copyBtn.innerHTML;
                        copyBtn.innerHTML = '<i class="fas fa-check me-2"></i>¡Copiado!';
                        copyBtn.classList.remove('btn-outline-secondary');
                        copyBtn.classList.add('btn-success');
                        
                        setTimeout(() => {
                            copyBtn.innerHTML = originalText;
                            copyBtn.classList.remove('btn-success');
                            copyBtn.classList.add('btn-outline-secondary');
                        }, 2000);
                    });
                });
            }
        });

        // Opción para compartir
        function shareReserva() {
            if (navigator.share) {
                navigator.share({
                    title: 'Mi Reserva Hotel ICI',
                    text: '¡He reservado en Hotel ICI! Número de reserva: #{{ isset($reserva) ? str_pad($reserva->id, 6, '0', STR_PAD_LEFT) : "" }}',
                    url: window.location.href,
                })
                .then(() => console.log('Compartido exitosamente'))
                .catch((error) => console.log('Error al compartir:', error));
            } else {
                alert('La función de compartir no está disponible en este navegador');
            }
        }
    </script>
</body>
</html>