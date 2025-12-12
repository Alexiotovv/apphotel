<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Muestra el formulario de registro.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Procesa el registro de un nuevo usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'dni' => 'required|string|max:20|unique:clientes,dni',
            'telefono' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Crear usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_admin' => false,
            ]);

            // Crear cliente
            $cliente = Cliente::create([
                'nombre' => $request->name,
                'apellido' => $request->apellido ?? '',
                'dni' => $request->dni,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'direccion' => $request->direccion ?? '',
            ]);

            // Asociar cliente al usuario
            $user->cliente_id = $cliente->id;
            $user->save();

            // Auto-login
            auth()->login($user);

            return redirect()->route('cliente.reservas.index')
                ->with('success', '¡Registro exitoso! Bienvenido ' . $user->name);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al registrar: ' . $e->getMessage())
                ->withInput();
        }
    }
}