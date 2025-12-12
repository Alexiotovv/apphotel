@extends('layouts.cliente')

@section('title', 'Editar Perfil')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">
                    <i class="fas fa-user-edit me-2"></i>Editar Perfil
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('cliente.perfil.show') }}">Mi Perfil</a></li>
                        <li class="breadcrumb-item active">Editar</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('cliente.perfil.show') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Editar Información Personal</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('cliente.perfil.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="nombre" class="form-control" 
                                   value="{{ old('nombre', $cliente->nombre) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido *</label>
                            <input type="text" name="apellido" class="form-control" 
                                   value="{{ old('apellido', $cliente->apellido) }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">DNI *</label>
                            <input type="text" name="dni" class="form-control" 
                                   value="{{ old('dni', $cliente->dni) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono *</label>
                            <input type="tel" name="telefono" class="form-control" 
                                   value="{{ old('telefono', $cliente->telefono) }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" 
                                   value="{{ old('email', $cliente->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" class="form-control" 
                                   value="{{ old('fecha_nacimiento', $cliente->fecha_nacimiento) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <textarea name="direccion" class="form-control" rows="2">{{ old('direccion', $cliente->direccion) }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Preferencias</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="preferencias[]" value="notificaciones_email" 
                                   id="notificaciones_email" {{ in_array('notificaciones_email', json_decode($cliente->preferencias ?? '[]', true) ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label" for="notificaciones_email">
                                Recibir notificaciones por email
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="preferencias[]" value="ofertas_especiales" 
                                   id="ofertas_especiales" {{ in_array('ofertas_especiales', json_decode($cliente->preferencias ?? '[]', true) ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label" for="ofertas_especiales">
                                Recibir ofertas especiales
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="preferencias[]" value="newsletter" 
                                   id="newsletter" {{ in_array('newsletter', json_decode($cliente->preferencias ?? '[]', true) ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label" for="newsletter">
                                Suscribirme al newsletter
                            </label>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <small>Al cambiar tu email, también se actualizará en tu cuenta de usuario.</small>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('cliente.perfil.show') }}" class="btn btn-secondary me-md-2">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection