<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mi Cuenta - Hotel ICI')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            min-height: 100vh;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .sidebar .nav-link {
            color: #333;
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover {
            background: #f1f5fd;
            border-left: 3px solid #4a6fa5;
        }
        .sidebar .nav-link.active {
            background: #f1f5fd;
            border-left: 3px solid #4a6fa5;
            color: #4a6fa5;
            font-weight: 600;
        }
        .main-content {
            padding: 20px;
        }
        .user-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .badge-estado {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
        }
        .badge-pendiente { background: #fff3cd; color: #856404; }
        .badge-confirmada { background: #d4edda; color: #155724; }
        .badge-cancelada { background: #f8d7da; color: #721c24; }
        .qr-code {
            max-width: 200px;
            margin: 0 auto;
            padding: 10px;
            background: white;
            border-radius: 10px;
        }
    </style>
    @yield('css')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <!-- Información del Usuario -->
                    <div class="user-card">
                        <div class="text-center mb-3">
                            <div class="mb-2">
                                <i class="fas fa-user-circle fa-3x"></i>
                            </div>
                            <h5 class="mb-0">{{ Auth::user()->name }}</h5>
                            <small>Cliente</small>
                        </div>
                        <div class="text-center">
                            <small><i class="fas fa-envelope"></i> {{ Auth::user()->email }}</small>
                        </div>
                    </div>

                    <!-- Menú de Navegación -->
                    <nav class="nav flex-column">
                        <a class="nav-link {{ request()->routeIs('cliente.reservas.*') ? 'active' : '' }}" 
                        href="{{ route('cliente.reservas.index') }}">
                            <i class="fas fa-calendar-alt me-2"></i> Mis Reservas
                        </a>
                        <a class="nav-link {{ request()->routeIs('cliente.perfil.*') ? 'active' : '' }}" 
                        href="{{ route('cliente.perfil.show') }}">
                            <i class="fas fa-user me-2"></i> Mi Perfil
                        </a>
                        <a class="nav-link {{ request()->routeIs('cliente.pagos.*') ? 'active' : '' }}" 
                        href="{{ route('cliente.pagos.index') }}">
                            <i class="fas fa-history me-2"></i> Historial de Pagos
                        </a>
                        <a class="nav-link {{ request()->routeIs('cliente.facturas.*') ? 'active' : '' }}" 
                        href="{{ route('cliente.facturas.index') }}">
                            <i class="fas fa-file-invoice me-2"></i> Facturas
                        </a>
                        <a class="nav-link" href="{{ url('/') }}" target="_blank">
                            <i class="fas fa-home me-2"></i> Ir al Sitio Web
                        </a>
                        <hr>
                        <!-- ... logout ... -->
                    </nav>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content">
                    <!-- Alertas -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            ✅ {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            ❌ {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show">
                            ℹ️ {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @yield('scripts')
</body>
</html>