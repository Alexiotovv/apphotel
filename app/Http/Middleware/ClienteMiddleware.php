<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ClienteMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión primero.');
        }

        $user = Auth::user();
        
        // Si es admin, redirigir al panel admin
        if ($user->is_admin) {
            return redirect()->route('admin.reservas.index')
                ->with('info', 'Ya estás autenticado como administrador.');
        }

        // Verificar que tenga cliente_id asignado o encontrar por email
        if (!$user->cliente_id) {
            $cliente = \App\Models\Cliente::where('email', $user->email)->first();
            
            if (!$cliente) {
                // Crear cliente automáticamente si no existe
                $cliente = \App\Models\Cliente::create([
                    'nombre' => $user->name,
                    'email' => $user->email,
                    'dni' => '00000000', // DNI temporal
                    'telefono' => 'Sin teléfono',
                ]);
            }
            
            $user->cliente_id = $cliente->id;
            $user->save();
        }

        return $next($request);
    }
}