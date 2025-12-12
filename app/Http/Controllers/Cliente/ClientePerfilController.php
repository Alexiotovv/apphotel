<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ClientePerfilController extends Controller
{
    // Mostrar perfil del cliente
    public function show()
    {
        $user = Auth::user();
        $cliente = Cliente::find($user->cliente_id);
        
        if (!$cliente) {
            return redirect()->route('cliente.reservas.index')
                ->with('error', 'No se encontró tu información de cliente.');
        }
        
        // Estadísticas
        $estadisticas = [
            'total_reservas' => \App\Models\Reserva::where('cliente_id', $cliente->id)->count(),
            'reservas_confirmadas' => \App\Models\Reserva::where('cliente_id', $cliente->id)
                ->where('estado', 'confirmada')->count(),
            'total_gastado' => \App\Models\Reserva::where('cliente_id', $cliente->id)
                ->where('estado', 'confirmada')
                ->sum('precio_total'),
            'ultima_reserva' => \App\Models\Reserva::where('cliente_id', $cliente->id)
                ->latest()
                ->first(),
        ];
        
        return view('cliente.perfil.show', compact('user', 'cliente', 'estadisticas'));
    }

    // Mostrar formulario de edición
    public function edit()
    {
        $user = Auth::user();
        $cliente = Cliente::find($user->cliente_id);
        
        if (!$cliente) {
            return redirect()->route('cliente.reservas.index')
                ->with('error', 'No se encontró tu información de cliente.');
        }
        
        return view('cliente.perfil.edit', compact('user', 'cliente'));
    }

    // Actualizar perfil
    public function update(Request $request)
    {
        $user = Auth::user();
        $cliente = Cliente::find($user->cliente_id);
        
        if (!$cliente) {
            return redirect()->route('cliente.perfil.show')
                ->with('error', 'No se encontró tu información de cliente.');
        }
        
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'dni' => 'required|string|max:20|unique:clientes,dni,' . $cliente->id,
            'telefono' => 'required|string|max:20',
            'email' => 'required|email|unique:clientes,email,' . $cliente->id,
            'direccion' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'preferencias' => 'nullable|array',
        ]);
        
        try {
            // Actualizar cliente
            $cliente->update([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'dni' => $request->dni,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'direccion' => $request->direccion,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'preferencias' => $request->preferencias ? json_encode($request->preferencias) : null,
            ]);
            
            // Actualizar usuario si el email cambió
            if ($user->email != $request->email) {
                $user->email = $request->email;
                $user->save();
            }
            
            Log::info('Perfil actualizado', [
                'cliente_id' => $cliente->id,
                'user_id' => $user->id,
            ]);
            
            return redirect()->route('cliente.perfil.show')
                ->with('success', 'Perfil actualizado exitosamente.');
                
        } catch (\Exception $e) {
            Log::error('Error actualizando perfil', [
                'error' => $e->getMessage(),
                'cliente_id' => $cliente->id,
            ]);
            
            return back()->with('error', 'Error al actualizar el perfil: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Actualizar contraseña
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = Auth::user();
        
        // Verificar contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.']);
        }
        
        try {
            // Actualizar contraseña
            $user->password = Hash::make($request->password);
            $user->save();
            
            Log::info('Contraseña actualizada', [
                'user_id' => $user->id,
            ]);
            
            return redirect()->route('cliente.perfil.show')
                ->with('success', 'Contraseña actualizada exitosamente.');
                
        } catch (\Exception $e) {
            Log::error('Error actualizando contraseña', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            
            return back()->with('error', 'Error al actualizar la contraseña: ' . $e->getMessage());
        }
    }
}