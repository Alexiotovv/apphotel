<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión primero.');
        }

        if (!Auth::user()->is_admin) {
            // Si no es admin, redirigir al área de cliente
            return redirect()->route('cliente.reservas.index')
                ->with('error', 'No tienes permisos de administrador.');
        }

        return $next($request);
    }
}