<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ClienteCompletoMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Solo aplica a usuarios no admin
        if (!$user->is_admin && !$user->cliente_id) {
            return redirect()->route('cliente.completar-perfil')
                ->with('info', 'Completa tu información personal para realizar reservas.');
        }

        return $next($request);
    }
}