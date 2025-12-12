{{-- resources/views/reservas/create.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar - Hotel ICI</title>
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
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://placehold.co/1920x600/4a6fa5/white?text=Hotel+ICI+-+Reserva+Ahora') no-repeat center center/cover;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            margin-bottom: 30px;
        }
        .reserva-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        .section-title {
            position: relative;
            margin-bottom: 2rem;
        }
        .section-title:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 3px;
            background: #4a6fa5;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin: 20px 0 40px;
            gap: 20px;
        }
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #666;
            margin-bottom: 8px;
            border: 3px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .step.active .step-circle {
            background: #4a6fa5;
            color: white;
        }
        .step-line {
            position: absolute;
            top: 20px;
            left: 50%;
            width: 120px;
            height: 2px;
            background: #e0e0e0;
            z-index: -1;
        }
        .step:not(:last-child) .step-line {
            display: block;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #4a6fa5;
            box-shadow: 0 0 0 0.2rem rgba(74, 111, 165, 0.25);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .btn-primary {
            background: #4a6fa5;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: #3a5a85;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 111, 165, 0.3);
        }
        .btn-outline-secondary {
            border: 2px solid #6c757d;
            color: #6c757d;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-outline-secondary:hover {
            background: #6c757d;
            color: white;
        }
        .info-box {
            background: linear-gradient(to right, #e3f2fd, #f3e5f5);
            border-left: 4px solid #4a6fa5;
            border-radius: 8px;
            padding: 20px;
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
                
                @auth
                    <!-- Usuario autenticado -->
                    @if(auth()->user()->is_admin)
                        <li class="nav-item">
                            <a href="{{ route('admin.reservas.index') }}" class="nav-link">
                                <i class="fas fa-user-shield"></i> Panel Admin
                            </a>
                        </li>
                    @else
                        <!-- Cliente -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('cliente.reservas.index') }}">
                                        <i class="fas fa-calendar-alt me-2"></i> Mis Reservas
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}" 
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                @else
                    <!-- Usuario no autenticado -->
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="nav-link">
                            <i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('register') }}" class="nav-link">
                            <i class="fas fa-user-plus me-1"></i> Registrarse
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a href="{{ route('login') }}" class="nav-link">
                            <i class="fas fa-lock me-1"></i> Acceso Admin
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

    <!-- Hero Banner -->
    <section class="hero-section">
        <div class="container">
            <h1 class="display-5 fw-bold">
                <i class="fas fa-calendar-check me-3"></i>Realizar Reserva
            </h1>
            <p class="lead">Complete el formulario para reservar su estadía</p>
        </div>
    </section>

    <!-- Alertas -->
    <div class="container">
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-exclamation-triangle me-2"></i>Hay errores en el formulario:
                </h5>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-check-circle me-2"></i>¡Éxito!
                </h5>
                <p class="mb-0">{{ session('success') }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-times-circle me-2"></i>¡Error!
                </h5>
                <p class="mb-0">{{ session('error') }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- Formulario de Reserva -->
    <div class="container">
        <div class="reserva-card p-4 p-md-5">
            <!-- Indicador de Pasos -->
            <div class="step-indicator">
                <div class="step active">
                    <div class="step-circle">1</div>
                    <span class="text-muted">Datos Personales</span>
                </div>
                <div class="step">
                    <div class="step-circle">2</div>
                    <span class="text-muted">Detalles Reserva</span>
                </div>
                <div class="step">
                    <div class="step-circle">3</div>
                    <span class="text-muted">Confirmación</span>
                </div>
            </div>

            <form method="POST" action="{{ route('reservas.store') }}" id="reservaForm">
                @csrf

                <!-- Datos Personales -->
                <h4 class="section-title">
                    <i class="fas fa-user-circle me-2"></i>Datos Personales
                </h4>

                @if(isset($cliente) && $cliente)
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Tus datos se han cargado automáticamente desde tu perfil. Puedes modificarlos si es necesario.
                </div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="nombre" 
                            value="{{ old('nombre', $cliente->nombre ?? Auth::user()->name ?? '') }}" 
                            class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apellido *</label>
                        <input type="text" name="apellido" 
                            value="{{ old('apellido', $cliente->apellido ?? '') }}" 
                            class="form-control" required>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">DNI *</label>
                        <input type="text" name="dni" 
                            value="{{ old('dni', $cliente->dni ?? '') }}" 
                            class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono *</label>
                        <input type="tel" name="telefono" 
                            value="{{ old('telefono', $cliente->telefono ?? '') }}" 
                            class="form-control" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" 
                        value="{{ old('email', $cliente->email ?? Auth::user()->email ?? '') }}" 
                        class="form-control" required>
                    <small class="text-muted">Se enviará la confirmación a este email</small>
                </div>

                <hr class="my-4">

                <!-- Detalles de la Reserva -->
                <h4 class="section-title">
                    <i class="fas fa-calendar-alt me-2"></i>Detalles de la Reserva
                </h4>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Fecha de Entrada *</label>
                        <input type="date" 
                            name="fecha_entrada" 
                            class="form-control" 
                            value="{{ date('Y-m-d') }}" 
                            min="{{ date('Y-m-d') }}" 
                            required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha de Salida *</label>
                        <input type="date" 
                            name="fecha_salida" 
                            class="form-control" 
                            value="{{ date('Y-m-d', strtotime('+2 days')) }}" 
                            min="{{ date('Y-m-d', strtotime('+1 day')) }}" 
                            required>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Habitación *</label>
                        <select name="habitacion_id" class="form-select" required>
                            <option value="">Seleccione una habitación</option>
                            @foreach($habitaciones as $hab)
                                <option value="{{ $hab->id }}" 
                                    {{ $habitacionSeleccionada && $habitacionSeleccionada->id == $hab->id ? 'selected' : '' }}>
                                    {{ $hab->tipo }} - ${{ $hab->precio_noche }}/noche
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Huéspedes</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small">Adultos *</label>
                                <select name="adultos" class="form-select" required>
                                    @for($i = 1; $i <= 4; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Niños</label>
                                <select name="ninos" class="form-select">
                                    @for($i = 0; $i <= 3; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-sticky-note me-1"></i>Notas adicionales
                    </label>
                    <textarea name="notas" class="form-control" rows="3" placeholder="Comentarios especiales, requerimientos de cama, alergias alimentarias, etc..."></textarea>
                </div>

                <!-- Información Importante -->
                <div class="info-box mb-4">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-info-circle me-2"></i>Información importante:
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="mb-0">
                                <li><strong>Check-in:</strong> 15:00 hs</li>
                                <li><strong>Check-out:</strong> 11:00 hs</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="mb-0">
                                <li>Se requiere DNI al ingresar</li>
                                <li>Confirmación por email</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4 pt-3 border-top">
                    <a href="{{ url('/') }}" class="btn btn-outline-secondary me-md-2">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i>Enviar Reserva
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2025 Hotel ICI. Todos los derechos reservados.</p>
            <p>Diseñado para el Trabajo Final de Curso - ICI</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación de fechas
        document.addEventListener('DOMContentLoaded', function() {
            const fechaEntrada = document.querySelector('input[name="fecha_entrada"]');
            const fechaSalida = document.querySelector('input[name="fecha_salida"]');
            
            // Establecer fecha mínima de salida
            fechaEntrada.addEventListener('change', function() {
                const entrada = new Date(this.value);
                entrada.setDate(entrada.getDate() + 1);
                const minSalida = entrada.toISOString().split('T')[0];
                fechaSalida.min = minSalida;
                
                // Si la fecha de salida actual es menor que la nueva mínima, actualizarla
                if (new Date(fechaSalida.value) < entrada) {
                    fechaSalida.value = minSalida;
                }
            });
            
            // Inicializar fecha mínima de salida
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            fechaSalida.min = tomorrow.toISOString().split('T')[0];
        });
    </script>
</body>
</html>