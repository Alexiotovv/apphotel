<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Hotel ICI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
            max-width: 500px;
        }
        .register-header {
            background: linear-gradient(to right, #28a745, #20c997);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .register-body {
            padding: 30px;
        }
        .btn-register {
            background: linear-gradient(to right, #28a745, #20c997);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            width: 100%;
            font-weight: 600;
        }
        .btn-register:hover {
            background: linear-gradient(to right, #218838, #1ba87e);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="register-card">
                    <div class="register-header">
                        <h1><i class="fas fa-user-plus"></i> Crear Cuenta</h1>
                        <p class="mb-0">Regístrate para gestionar tus reservas</p>
                    </div>
                    
                    <div class="register-body">
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <h5 class="mb-3">👤 Información Personal</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre *</label>
                                    <input type="text" name="name" class="form-control" 
                                           value="{{ old('name') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Apellido</label>
                                    <input type="text" name="apellido" class="form-control" 
                                           value="{{ old('apellido') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">DNI *</label>
                                    <input type="text" name="dni" class="form-control" 
                                           value="{{ old('dni') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Teléfono *</label>
                                    <input type="tel" name="telefono" class="form-control" 
                                           value="{{ old('telefono') }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Dirección</label>
                                <input type="text" name="direccion" class="form-control" 
                                       value="{{ old('direccion') }}">
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">🔐 Datos de la Cuenta</h5>
                            <div class="mb-3">
                                <label class="form-label">📧 Correo Electrónico *</label>
                                <input type="email" name="email" class="form-control" 
                                       value="{{ old('email') }}" required>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Contraseña *</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirmar Contraseña *</label>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <small>Al registrarte, podrás ver y pagar tus reservas, recibir confirmaciones por email y acceder a promociones exclusivas.</small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-register">
                                    <i class="fas fa-user-plus"></i> Crear Cuenta
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-0">
                                ¿Ya tienes cuenta? 
                                <a href="{{ route('login') }}" class="text-decoration-none">
                                    Inicia sesión aquí
                                </a>
                            </p>
                            <p class="mt-2 mb-0">
                                <a href="{{ url('/') }}" class="text-decoration-none">
                                    <i class="fas fa-home"></i> Volver al inicio
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>