<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Cliente;

class LoginController extends Controller
{
    /**
     * Muestra el formulario de login.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Procesa el intento de inicio de sesión.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        // Intentar autenticar al usuario
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            // Regenerar la sesión para prevenir fijación de sesión
            $request->session()->regenerate();

            $user = Auth::user();

            // Redirigir según el rol
            if ($user->is_admin) {
                return redirect()->intended('/admin/habitaciones')
                    ->with('success', '¡Bienvenido Administrador!');
            }

            // Si es cliente, verificar/crear registro en tabla clientes
            if (!$user->cliente_id) {
                $cliente = Cliente::where('email', $user->email)->first();
                
                if (!$cliente) {
                    // Crear cliente automáticamente
                    $cliente = Cliente::create([
                        'nombre' => $user->name,
                        'email' => $user->email,
                        'dni' => 'PENDIENTE-' . time(),
                        'telefono' => 'Sin especificar',
                        'direccion' => 'Sin especificar',
                    ]);
                }
                
                // Asociar cliente al usuario
                $user->cliente_id = $cliente->id;
                $user->save();
            }

            // Redirigir al área de cliente
            return redirect()->intended('/cliente/reservas')
                ->with('success', '¡Bienvenido ' . $user->name . '!');
        }

        // Si falla, lanzar error de validación
        throw ValidationException::withMessages([
            'email' => ['Las credenciales no coinciden con nuestros registros.'],
        ]);
    }

    /**
     * Cierra la sesión del usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('info', 'Sesión cerrada exitosamente.');
    }
}