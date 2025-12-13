<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ReservaController extends Controller
{
    // Mostrar formulario de reserva - SOLO PARA USUARIOS AUTENTICADOS
    public function create($habitacion_id = null)
    {
        // Verificar que el usuario esté autenticado
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('info', 'Debes iniciar sesión para realizar una reserva.');
        }

        $user = Auth::user();
        
        // Verificar si el usuario ya tiene un cliente asociado
        if (!$user->cliente_id) {
            // Buscar cliente por email o crear uno nuevo
            $cliente = Cliente::where('email', $user->email)->first();
            
            if (!$cliente) {
                // Crear cliente automáticamente
                $cliente = Cliente::create([
                    'nombre' => $user->name,
                    'email' => $user->email,
                    'dni' => 'PENDIENTE-' . time(),
                    'telefono' => 'No especificado',
                    'direccion' => 'No especificada',
                ]);
                
                // Asociar cliente al usuario
                $user->cliente_id = $cliente->id;
                $user->save();
            } else {
                // Asociar cliente existente al usuario
                $user->cliente_id = $cliente->id;
                $user->save();
            }
        }

        $habitaciones = Habitacion::where('disponible', true)->get();
        $habitacionSeleccionada = $habitacion_id 
            ? Habitacion::find($habitacion_id) 
            : null;
        
        // Obtener datos del cliente para prellenar el formulario
        $cliente = $user->cliente_id ? Cliente::find($user->cliente_id) : null;
            
        return view('reservas.create', compact('habitaciones', 'habitacionSeleccionada', 'cliente'));
    }

    public function store(Request $request)
    {
        // Verificar autenticación
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para realizar una reserva.');
        }

        $user = Auth::user();
        
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'dni' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
            'email' => 'required|email',
            'habitacion_id' => 'required|exists:habitaciones,id',
            'fecha_entrada' => 'required|date|after:yesterday',
            'fecha_salida' => 'required|date|after:fecha_entrada',
            'adultos' => 'required|integer|min:1|max:4',
            'ninos' => 'integer|min:0|max:3',
            'notas' => 'nullable|string|max:500',
        ]);

        try {
            // Verificar que el DNI sea único (excepto para el propio cliente del usuario)
            $clienteExistente = null;
            if ($user->cliente_id) {
                $clienteExistente = Cliente::find($user->cliente_id);
            }
            
            // Si el cliente existe y el DNI es diferente, verificar que no esté en uso
            if ($clienteExistente && $clienteExistente->dni != $request->dni) {
                $dniExistente = Cliente::where('dni', $request->dni)
                    ->where('id', '!=', $clienteExistente->id)
                    ->exists();
                    
                if ($dniExistente) {
                    return back()
                        ->withInput()
                        ->withErrors(['dni' => 'Este DNI ya está registrado por otro cliente.']);
                }
            }

            // Calcular noches y precio total
            $fechaEntrada = \Carbon\Carbon::parse($request->fecha_entrada);
            $fechaSalida = \Carbon\Carbon::parse($request->fecha_salida);
            $noches = $fechaEntrada->diffInDays($fechaSalida);
            
            $habitacion = Habitacion::findOrFail($request->habitacion_id);
            $precioTotal = $noches * $habitacion->precio_noche;

            // Usar el cliente existente o crear uno nuevo
            if ($user->cliente_id && $clienteExistente) {
                // Actualizar datos del cliente existente
                $clienteExistente->update([
                    'nombre' => $request->nombre,
                    'apellido' => $request->apellido,
                    'dni' => $request->dni,
                    'telefono' => $request->telefono,
                    'email' => $request->email,
                    'direccion' => $request->direccion ?? $clienteExistente->direccion,
                ]);
                $cliente = $clienteExistente;
            } else {
                // Crear nuevo cliente
                $cliente = Cliente::create([
                    'nombre' => $request->nombre,
                    'apellido' => $request->apellido,
                    'dni' => $request->dni,
                    'telefono' => $request->telefono,
                    'email' => $request->email,
                    'direccion' => $request->direccion ?? null,
                ]);
                
                // Asociar cliente al usuario
                $user->cliente_id = $cliente->id;
                $user->save();
            }

            // Crear reserva
            $reserva = Reserva::create([
                'cliente_id' => $cliente->id,
                'habitacion_id' => $request->habitacion_id,
                'fecha_entrada' => $request->fecha_entrada,
                'fecha_salida' => $request->fecha_salida,
                'noches' => $noches,
                'adultos' => $request->adultos,
                'ninos' => $request->ninos ?? 0,
                'precio_total' => $precioTotal,
                'estado' => 'pendiente',
                'notas' => $request->notas,
            ]);

            // Crear venta
            $venta = Venta::create([
                'cliente_id' => $cliente->id,
                'monto_total' => $precioTotal,
                'metodo_pago' => 'efectivo',
                'estado' => 'pendiente',
            ]);

            // Crear detalle de venta
            VentaDetalle::create([
                'venta_id' => $venta->id,
                'habitacion_id' => $request->habitacion_id,
                'servicio_id' => null,
                'cantidad' => $noches,
                'precio_unitario' => $habitacion->precio_noche,
                'subtotal' => $precioTotal,
            ]);

            // Enviar a Telegram
            
            
            return redirect()->route('reservas.gracias', ['id' => $reserva->id])
                ->with('success', '¡Reserva enviada exitosamente! Ya puedes verla y pagarla en "Mis Reservas".');

        } catch (\Exception $e) {
            \Log::error('Error completo: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile());
            \Log::error('Line: ' . $e->getLine());
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }




    public function gracias($id)
    {
        $reserva = Reserva::with(['cliente', 'habitacion'])->findOrFail($id);
        return view('reservas.gracias', compact('reserva'));
    }
}