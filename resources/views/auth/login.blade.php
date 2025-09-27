@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width: 400px;">
    <div class="card">
        <div class="card-header text-center">
            <h4>🔐 Iniciar Sesión</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Correo</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Ingresar</button>
            </form>
            @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    Credenciales incorrectas.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection