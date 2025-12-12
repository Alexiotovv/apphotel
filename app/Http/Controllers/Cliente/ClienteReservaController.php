<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Venta;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClienteReservaController extends Controller
{

    // Mostrar todas las reservas del cliente
    public function index()
    {
        $user = Auth::user();
        
        if (!$user->cliente_id) {
            return redirect()->route('home')->with('error', 'No tienes información de cliente asociada.');
        }
        
        $reservas = Reserva::with(['habitacion', 'venta', 'venta.pagos'])
            ->where('cliente_id', $user->cliente_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('cliente.reservas.index', compact('reservas'));
    }

    // Mostrar detalles de una reserva específica
    public function show($id)
    {
        $user = Auth::user();
        
        $reserva = Reserva::with(['habitacion', 'venta', 'venta.pagos', 'cliente'])
            ->where('cliente_id', $user->cliente_id)
            ->findOrFail($id);
            
        return view('cliente.reservas.show', compact('reserva'));
    }

    // Mostrar formulario de pago
    public function pagar($id)
    {
        $user = Auth::user();
        $reserva = Reserva::with(['habitacion', 'venta'])
            ->where('cliente_id', $user->cliente_id)
            ->findOrFail($id);
            
        // Verificar que la reserva esté pendiente de pago
        if ($reserva->estado == 'cancelada') {
            return redirect()->route('cliente.reservas.show', $id)
                ->with('error', 'Esta reserva ha sido cancelada.');
        }
        
        // Verificar si ya tiene un pago completado
        if ($reserva->venta && $reserva->venta->estado == 'completada') {
            return redirect()->route('cliente.reservas.show', $id)
                ->with('info', 'Esta reserva ya ha sido pagada.');
        }
            
        return view('cliente.reservas.pagar', compact('reserva'));
    }

   
    public function procesarPagoTarjeta(Request $request, $id)
    {
        $request->validate([
            'numero_tarjeta' => 'required|string|min:16|max:19',
            'nombre_titular' => 'required|string|max:100',
            'mes_expiracion' => 'required|string',
            'ano_expiracion' => 'required|string',
            'cvv' => 'required|string|min:3|max:4',
        ]);
        
        $user = Auth::user();
        
        // Log inicial
        $logData = [
            'user_id' => $user->id,
            'user_cliente_id' => $user->cliente_id,
            'reserva_id' => $id,
            'timestamp' => now()->toDateTimeString(),
        ];
        
        $this->logToFile('pago_inicio', $logData);
        
        // Obtener reserva SIN with venta primero
        $reserva = Reserva::where('cliente_id', $user->cliente_id)
            ->findOrFail($id);
        
        $logData['reserva_data'] = [
            'id' => $reserva->id,
            'cliente_id' => $reserva->cliente_id,
            'venta_id' => $reserva->venta_id,
            'precio_total' => $reserva->precio_total,
            'estado' => $reserva->estado,
        ];
        
        $this->logToFile('pago_reserva_obtenida', $logData);
            
        try {
            // VERIFICAR SI LA VENTA EXISTE
            $venta = null;
            
            if ($reserva->venta_id) {
                // Buscar venta por ID
                $venta = Venta::find($reserva->venta_id);
                
                $this->logToFile('pago_venta_buscada', [
                    'reserva_venta_id' => $reserva->venta_id,
                    'venta_encontrada' => !is_null($venta),
                    'venta_data' => $venta ? $venta->toArray() : null,
                ]);
            }
            
            // Si no hay venta_id o no se encontró la venta
            if (!$venta) {
                $this->logToFile('pago_creando_venta', [
                    'razon' => $reserva->venta_id ? 'Venta no encontrada' : 'Sin venta_id',
                    'reserva_venta_id' => $reserva->venta_id,
                ]);
                
                // Crear venta
                $venta = Venta::create([
                    'cliente_id' => $reserva->cliente_id,
                    'monto_total' => $reserva->precio_total,
                    'metodo_pago' => 'credito',
                    'estado' => 'pendiente',
                ]);
                
                $this->logToFile('pago_venta_creada', [
                    'venta_id' => $venta->id,
                    'venta_data' => $venta->toArray(),
                ]);
                
                // Asociar venta a reserva
                $reserva->venta_id = $venta->id;
                $reserva->save();
                
                $this->logToFile('pago_reserva_actualizada', [
                    'nuevo_venta_id' => $reserva->venta_id,
                    'reserva_actualizada' => $reserva->toArray(),
                ]);
                
                // Recargar la reserva
                $reserva->refresh();
            }
            
            // Verificar nuevamente
            if (!$venta) {
                throw new \Exception('No se pudo crear o asociar la venta a la reserva.');
            }
            
            $this->logToFile('pago_venta_confirmada', [
                'venta_id' => $venta->id,
                'reserva_venta_id' => $reserva->venta_id,
                'son_iguales' => $reserva->venta_id == $venta->id,
            ]);
            
            // Generar referencia única
            $referencia = 'TARJ-' . strtoupper(uniqid());
            
            $this->logToFile('pago_creando_pago', [
                'referencia' => $referencia,
                'monto' => $reserva->precio_total,
            ]);
            
            // Crear registro de pago
            $pago = Pago::create([
                'venta_id' => $venta->id,
                'metodo_pago' => 'tarjeta',
                'monto' => $reserva->precio_total,
                'estado' => 'completado',
                'referencia' => $referencia,
                'detalles' => json_encode([
                    'tarjeta_ultimos_4' => substr($request->numero_tarjeta, -4),
                    'nombre_titular' => $request->nombre_titular,
                    'fecha_expiracion' => $request->mes_expiracion . '/' . $request->ano_expiracion,
                    'procesado_en' => now()->format('Y-m-d H:i:s'),
                ]),
            ]);
            
            $this->logToFile('pago_creado', [
                'pago_id' => $pago->id,
                'pago_data' => $pago->toArray(),
            ]);
            
            // Actualizar venta
            $venta->update([
                'estado' => 'completada',
                'metodo_pago' => 'credito',
                'monto_total' => $reserva->precio_total,
            ]);
            
            $this->logToFile('pago_venta_actualizada', [
                'venta_actualizada' => $venta->fresh()->toArray(),
            ]);
            
            // Actualizar reserva
            $reserva->update([
                'estado' => 'confirmada',
            ]);
            
            $this->logToFile('pago_reserva_confirmada', [
                'reserva_confirmada' => $reserva->fresh()->toArray(),
            ]);
            
            // Log final exitoso
            $this->logToFile('pago_exitoso', [
                'reserva_id' => $reserva->id,
                'venta_id' => $venta->id,
                'pago_id' => $pago->id,
                'referencia' => $referencia,
            ]);
            
            return redirect()->route('cliente.reservas.comprobante', $pago->id)
                ->with('success', '¡Pago realizado exitosamente! Tu reserva ha sido confirmada.');
                
        } catch (\Exception $e) {
            // Log de error detallado
            $this->logToFile('pago_error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'reserva' => $reserva->toArray(),
                'user' => $user->toArray(),
                'request_data' => $request->except(['numero_tarjeta', 'cvv']), // Excluir datos sensibles
            ]);
            
            return back()->with('error', 'Error al procesar el pago: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Método para guardar logs en archivo
    private function logToFile($accion, $data)
    {
        $logFile = storage_path('logs/pagos_debug.log');
        
        $logEntry = [
            'timestamp' => now()->toDateTimeString(),
            'accion' => $accion,
            'data' => $data,
        ];
        
        // Convertir a JSON con formato legible
        $jsonLog = json_encode($logEntry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        // Agregar al archivo de log
        file_put_contents($logFile, $jsonLog . "\n---\n", FILE_APPEND);
    }

    // public function procesarPagoTarjeta(Request $request, $id)
    // {
    //     $request->validate([
    //         'numero_tarjeta' => 'required|string|min:16|max:19',
    //         'nombre_titular' => 'required|string|max:100',
    //         'mes_expiracion' => 'required|string',
    //         'ano_expiracion' => 'required|string',
    //         'cvv' => 'required|string|min:3|max:4',
    //     ]);
        
    //     $user = Auth::user();
    //     $reserva = Reserva::with(['venta']) // Cargar relación venta
    //         ->where('cliente_id', $user->cliente_id)
    //         ->findOrFail($id);
            
    //     \Log::info('Reserva para pago:', [
    //         'reserva_id' => $reserva->id,
    //         'venta_id' => $reserva->venta_id,
    //         'tiene_venta' => !is_null($reserva->venta),
    //     ]);
            
    //     try {
    //         // VERIFICAR SI LA VENTA EXISTE
    //         if (!$reserva->venta) {
    //             \Log::info('Creando nueva venta para reserva: ' . $reserva->id);
                
    //             // Crear venta
    //             $venta = Venta::create([
    //                 'cliente_id' => $reserva->cliente_id,
    //                 'monto_total' => $reserva->precio_total,
    //                 'metodo_pago' => 'credito',
    //                 'estado' => 'pendiente',
    //             ]);
                
    //             // Asociar venta a reserva
    //             $reserva->venta_id = $venta->id;
    //             $reserva->save();
                
    //             // Recargar la relación
    //             $reserva->refresh();
    //             $reserva->load('venta');
                
    //             \Log::info('Venta creada:', [
    //                 'venta_id' => $venta->id,
    //                 'nuevo_venta_id' => $reserva->venta_id,
    //             ]);
    //         }
            
    //         // Verificar nuevamente
    //         if (!$reserva->venta) {
    //             throw new \Exception('No se pudo crear o asociar la venta a la reserva.');
    //         }
            
    //         \Log::info('Procesando pago para venta: ' . $reserva->venta->id);
            
    //         // Generar referencia única
    //         $referencia = 'TARJ-' . strtoupper(uniqid());
            
    //         // Crear registro de pago
    //         $pago = Pago::create([
    //             'venta_id' => $reserva->venta->id,
    //             'metodo_pago' => 'tarjeta',
    //             'monto' => $reserva->precio_total,
    //             'estado' => 'completado',
    //             'referencia' => $referencia,
    //             'detalles' => json_encode([
    //                 'tarjeta_ultimos_4' => substr($request->numero_tarjeta, -4),
    //                 'nombre_titular' => $request->nombre_titular,
    //                 'fecha_expiracion' => $request->mes_expiracion . '/' . $request->ano_expiracion,
    //                 'procesado_en' => now()->format('Y-m-d H:i:s'),
    //             ]),
    //         ]);
            
    //         // Actualizar venta
    //         $reserva->venta->update([
    //             'estado' => 'completada',
    //             'metodo_pago' => 'credito',
    //             'monto_total' => $reserva->precio_total,
    //         ]);
            
    //         // Actualizar reserva
    //         $reserva->update([
    //             'estado' => 'confirmada',
    //         ]);
            
    //         \Log::info('Pago completado exitosamente:', [
    //             'pago_id' => $pago->id,
    //             'referencia' => $referencia,
    //         ]);
            
    //         return redirect()->route('cliente.reservas.comprobante', $pago->id)
    //             ->with('success', '¡Pago realizado exitosamente! Tu reserva ha sido confirmada.');
                
    //     } catch (\Exception $e) {
    //         \Log::error('Error procesando pago:', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //             'reserva' => $reserva->toArray(),
    //         ]);
            
    //         return back()->with('error', 'Error al procesar el pago: ' . $e->getMessage())
    //             ->withInput();
    //     }
    // }
    
    // public function procesarPagoTarjeta(Request $request, $id)
    // {
    //     $request->validate([
    //         'numero_tarjeta' => 'required|string|min:16|max:19',
    //         'nombre_titular' => 'required|string|max:100',
    //         'mes_expiracion' => 'required|string',
    //         'ano_expiracion' => 'required|string',
    //         'cvv' => 'required|string|min:3|max:4',
    //     ]);
        
    //     $user = Auth::user();
    //     $reserva = Reserva::with(['venta'])
    //         ->where('cliente_id', $user->cliente_id)
    //         ->findOrFail($id);
            
    //     try {
    //         // VERIFICAR SI LA VENTA EXISTE - SI NO, CREARLA
    //         if (!$reserva->venta) {
    //             // Crear venta si no existe
    //             $venta = Venta::create([
    //                 'cliente_id' => $reserva->cliente_id,
    //                 'monto_total' => $reserva->precio_total,
    //                 'metodo_pago' => 'credito',
    //                 'estado' => 'pendiente',
    //             ]);
                
    //             // Asociar la venta a la reserva si la columna existe
    //             try {
    //                 $reserva->venta_id = $venta->id;
    //                 $reserva->save();
    //             } catch (\Exception $e) {
    //                 // Si no existe la columna venta_id, no hay problema
    //                 \Log::info('No se pudo asociar venta_id a reserva: ' . $e->getMessage());
    //             }
                
    //             // Refrescar la relación
    //             $reserva->refresh();
    //             $reserva->load('venta');
    //         }
            
    //         // Ahora $reserva->venta debería existir
    //         if (!$reserva->venta) {
    //             throw new \Exception('No se pudo crear o encontrar la venta asociada.');
    //         }
            
    //         // Generar referencia única
    //         $referencia = 'TARJ-' . strtoupper(uniqid());
            
    //         // Crear registro de pago
    //         $pago = Pago::create([
    //             'venta_id' => $reserva->venta->id, // Ahora seguro que existe
    //             'metodo_pago' => 'tarjeta',
    //             'monto' => $reserva->precio_total,
    //             'estado' => 'completado',
    //             'referencia' => $referencia,
    //             'detalles' => json_encode([
    //                 'tarjeta_ultimos_4' => substr($request->numero_tarjeta, -4),
    //                 'nombre_titular' => $request->nombre_titular,
    //                 'fecha_expiracion' => $request->mes_expiracion . '/' . $request->ano_expiracion,
    //                 'procesado_en' => now()->format('Y-m-d H:i:s'),
    //             ]),
    //         ]);
            
    //         // Actualizar venta
    //         $reserva->venta->update([
    //             'estado' => 'completada',
    //             'metodo_pago' => 'credito',
    //             'monto_total' => $reserva->precio_total,
    //         ]);
            
    //         // Actualizar reserva
    //         $reserva->update([
    //             'estado' => 'confirmada',
    //         ]);
            
    //         // Registrar en log
    //         Log::info('Pago con tarjeta realizado', [
    //             'cliente_id' => $user->cliente_id,
    //             'reserva_id' => $reserva->id,
    //             'pago_id' => $pago->id,
    //             'monto' => $reserva->precio_total,
    //             'referencia' => $referencia,
    //         ]);
            
    //         return redirect()->route('cliente.reservas.comprobante', $pago->id)
    //             ->with('success', '¡Pago realizado exitosamente! Tu reserva ha sido confirmada.');
                
    //     } catch (\Exception $e) {
    //         Log::error('Error procesando pago con tarjeta', [
    //             'error' => $e->getMessage(),
    //             'reserva_id' => $id,
    //             'cliente_id' => $user->cliente_id,
    //             'reserva_data' => $reserva->toArray(),
    //         ]);
            
    //         return back()->with('error', 'Error al procesar el pago: ' . $e->getMessage())
    //             ->withInput();
    //     }
    // }
    
    // public function procesarPagoTarjeta(Request $request, $id)
    // {
    //     $request->validate([
    //         'numero_tarjeta' => 'required|string|min:16|max:19',
    //         'nombre_titular' => 'required|string|max:100',
    //         'mes_expiracion' => 'required|string',
    //         'ano_expiracion' => 'required|string',
    //         'cvv' => 'required|string|min:3|max:4',
    //     ]);
        
    //     $user = Auth::user();
    //     $reserva = Reserva::with(['venta'])
    //         ->where('cliente_id', $user->cliente_id)
    //         ->findOrFail($id);
            
    //     try {
    //         // Verificar que exista la venta
    //         if (!$reserva->venta) {
    //             // Crear venta si no existe
    //             $venta = Venta::create([
    //                 'cliente_id' => $reserva->cliente_id,
    //                 'monto_total' => $reserva->precio_total,
    //                 'metodo_pago' => 'credito',
    //                 'estado' => 'pendiente',
    //             ]);
                
    //             // Actualizar reserva con venta_id
    //             $reserva->venta_id = $venta->id;
    //             $reserva->save();
    //             $reserva->refresh();
    //         }
            
    //         // Aquí normalmente integrarías con una pasarela de pago como Stripe, PayPal, etc.
    //         // Por ahora simulamos el pago exitoso
            
    //         // Generar referencia única
    //         $referencia = 'TARJ-' . strtoupper(uniqid());
            
    //         // Crear registro de pago
    //         $pago = Pago::create([
    //             'venta_id' => $reserva->venta->id,
    //             'metodo_pago' => 'tarjeta',
    //             'monto' => $reserva->precio_total,
    //             'estado' => 'completado',
    //             'referencia' => $referencia,
    //             'detalles' => json_encode([
    //                 'tarjeta_ultimos_4' => substr($request->numero_tarjeta, -4),
    //                 'nombre_titular' => $request->nombre_titular,
    //                 'fecha_expiracion' => $request->mes_expiracion . '/' . $request->ano_expiracion,
    //                 'procesado_en' => now()->format('Y-m-d H:i:s'),
    //             ]),
    //         ]);
            
    //         // Actualizar venta
    //         $reserva->venta->update([
    //             'estado' => 'completada',
    //             'metodo_pago' => 'tarjeta',
    //             'monto_total' => $reserva->precio_total,
    //         ]);
            
    //         // Actualizar reserva
    //         $reserva->update([
    //             'estado' => 'confirmada',
    //         ]);
            
    //         // Notificar al admin
    //         $this->notificarPagoAdmin($reserva, $pago);
            
    //         // Registrar en log
    //         Log::info('Pago con tarjeta realizado', [
    //             'cliente_id' => $user->cliente_id,
    //             'reserva_id' => $reserva->id,
    //             'pago_id' => $pago->id,
    //             'monto' => $reserva->precio_total,
    //             'referencia' => $referencia,
    //         ]);
            
    //         return redirect()->route('cliente.reservas.comprobante', $pago->id)
    //             ->with('success', '¡Pago realizado exitosamente! Tu reserva ha sido confirmada.');
                
    //     } catch (\Exception $e) {
    //         Log::error('Error procesando pago con tarjeta', [
    //             'error' => $e->getMessage(),
    //             'reserva_id' => $id,
    //             'cliente_id' => $user->cliente_id,
    //         ]);
            
    //         return back()->with('error', 'Error al procesar el pago: ' . $e->getMessage())
    //             ->withInput();
    //     }
    // }

    // Procesar pago con QR
    public function procesarPagoQR(Request $request, $id)
    {
        $user = Auth::user();
        $reserva = Reserva::with(['venta'])
            ->where('cliente_id', $user->cliente_id)
            ->findOrFail($id);
            
        try {
            // Verificar que exista la venta
            if (!$reserva->venta) {
                // Crear venta si no existe
                $venta = Venta::create([
                    'cliente_id' => $reserva->cliente_id,
                    'monto_total' => $reserva->precio_total,
                    'metodo_pago' => 'qr',
                    'estado' => 'pendiente',
                ]);
                
                // Actualizar reserva con venta_id
                $reserva->venta_id = $venta->id;
                $reserva->save();
                $reserva->refresh();
            }
            
            // Generar datos para el QR
            $referencia = 'QR-' . strtoupper(uniqid());
            $qrData = [
                'reserva_id' => $reserva->id,
                'monto' => $reserva->precio_total,
                'cliente' => $user->name,
                'email' => $user->email,
                'fecha_generacion' => now()->format('Y-m-d H:i:s'),
                'referencia' => $referencia,
                'expira_en' => now()->addHours(1)->format('Y-m-d H:i:s'),
                'descripcion' => 'Pago reserva Hotel ICI - Hab: ' . $reserva->habitacion->tipo,
            ];
            
            // Guardar pago pendiente
            $pago = Pago::create([
                'venta_id' => $reserva->venta->id,
                'metodo_pago' => 'qr_pendiente',
                'monto' => $reserva->precio_total,
                'estado' => 'pendiente',
                'referencia' => $referencia,
                'detalles' => json_encode($qrData),
            ]);
            
            // Registrar en log
            Log::info('QR de pago generado', [
                'cliente_id' => $user->cliente_id,
                'reserva_id' => $reserva->id,
                'pago_id' => $pago->id,
                'referencia' => $referencia,
            ]);
            
            return redirect()->route('cliente.reservas.qr', $pago->id)
                ->with('info', 'Escanea el código QR con tu app bancaria para completar el pago.');
                
        } catch (\Exception $e) {
            Log::error('Error generando QR de pago', [
                'error' => $e->getMessage(),
                'reserva_id' => $id,
                'cliente_id' => $user->cliente_id,
            ]);
            
            return back()->with('error', 'Error al generar el código QR: ' . $e->getMessage());
        }
    }

    // Mostrar QR para pago
    public function mostrarQR($pagoId)
    {
        $pago = Pago::with(['venta'])->findOrFail($pagoId);
        $user = Auth::user();
        
        // Verificar que el pago pertenezca al cliente
        $reserva = Reserva::where('cliente_id', $user->cliente_id)
            ->whereHas('venta', function($q) use ($pago) {
                $q->where('id', $pago->venta_id);
            })->first();
            
        if (!$reserva) {
            abort(403, 'No tienes permiso para ver este QR.');
        }
        
        // Generar URL para el QR (simulación)
        // En producción, usarías una API de generación de QR como Google Charts
        $qrData = json_decode($pago->detalles, true);
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . 
                 urlencode(json_encode($qrData));
        
        return view('cliente.reservas.qr', compact('pago', 'reserva', 'qrUrl'));
    }

    // Verificar estado de pago QR (AJAX)
    public function verificarQR($pagoId)
    {
        $pago = Pago::with(['venta.reserva'])->findOrFail($pagoId);
        $user = Auth::user();
        
        // Verificar que el pago pertenezca al cliente
        if ($pago->venta->reserva->cliente_id != $user->cliente_id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        
        // Simular verificación de pago
        // En producción, aquí verificarías con la API del banco o pasarela de pago
        
        if ($pago->estado == 'pendiente') {
            // Para simular: después de 15 segundos se marca como pagado
            // En realidad esto lo haría un webhook del banco
            $tiempoTranscurrido = now()->diffInSeconds($pago->created_at);
            
            if ($tiempoTranscurrido > 15) {
                $pago->update([
                    'estado' => 'completado',
                    'metodo_pago' => 'qr',
                ]);
                
                $pago->venta->update([
                    'estado' => 'completada',
                    'metodo_pago' => 'qr',
                ]);
                
                $pago->venta->reserva->update([
                    'estado' => 'confirmada',
                ]);
                
                // Notificar al admin
                $this->notificarPagoAdmin($pago->venta->reserva, $pago);
                
                Log::info('Pago QR completado automáticamente (simulación)', [
                    'pago_id' => $pago->id,
                    'reserva_id' => $pago->venta->reserva->id,
                ]);
            }
        }
        
        return response()->json([
            'estado' => $pago->estado,
            'mensaje' => $pago->estado == 'completado' 
                ? '¡Pago confirmado!' 
                : 'Esperando confirmación del pago...',
            'redirect' => $pago->estado == 'completado' 
                ? route('cliente.reservas.comprobante', $pago->id)
                : null,
        ]);
    }

    // Mostrar comprobante de pago
    public function comprobante($pagoId)
    {
        $pago = Pago::with(['venta', 'venta.reserva', 'venta.reserva.habitacion'])
            ->findOrFail($pagoId);
            
        // Verificar que el pago pertenezca al cliente
        if ($pago->venta->reserva->cliente_id != Auth::user()->cliente_id) {
            abort(403, 'No tienes permiso para ver este comprobante.');
        }
            
        return view('cliente.reservas.comprobante', compact('pago'));
    }

    // Cancelar reserva (solo si está pendiente)
    public function cancelar(Request $request, $id)
    {
        $request->validate([
            'motivo' => 'nullable|string|max:255',
        ]);
        
        $user = Auth::user();
        $reserva = Reserva::with(['venta'])
            ->where('cliente_id', $user->cliente_id)
            ->where('estado', 'pendiente')
            ->findOrFail($id);
            
        $reserva->update([
            'estado' => 'cancelada',
            'notas' => ($reserva->notas ? $reserva->notas . "\n" : '') . 
                      '[CANCELADA POR CLIENTE] ' . ($request->motivo ?? 'Sin motivo especificado'),
        ]);
        
        if ($reserva->venta) {
            $reserva->venta->update([
                'estado' => 'cancelada',
            ]);
        }
        
        Log::info('Reserva cancelada por cliente', [
            'cliente_id' => $user->cliente_id,
            'reserva_id' => $reserva->id,
            'motivo' => $request->motivo,
        ]);
        
        return redirect()->route('cliente.reservas.index')
            ->with('success', 'Reserva cancelada exitosamente.');
    }

    // Solicitar factura
    public function solicitarFactura(Request $request, $id)
    {
        $request->validate([
            'email_factura' => 'required|email',
            'razon_social' => 'required|string|max:200',
            'rut' => 'required|string|max:20',
        ]);
        
        $user = Auth::user();
        $reserva = Reserva::with(['venta'])
            ->where('cliente_id', $user->cliente_id)
            ->where('estado', 'confirmada')
            ->findOrFail($id);
            
        if (!$reserva->venta) {
            return back()->with('error', 'No hay venta asociada a esta reserva.');
        }
        
        // Aquí normalmente generaría la factura en PDF y la enviaría por email
        // Por ahora solo marcamos como facturada y guardamos los datos
        
        $reserva->venta->update([
            'facturada' => true,
            'datos_facturacion' => json_encode([
                'email' => $request->email_factura,
                'razon_social' => $request->razon_social,
                'rut' => $request->rut,
                'fecha_solicitud' => now()->format('Y-m-d H:i:s'),
            ]),
        ]);
        
        Log::info('Factura solicitada', [
            'cliente_id' => $user->cliente_id,
            'reserva_id' => $reserva->id,
            'email_factura' => $request->email_factura,
        ]);
        
        return back()->with('success', 'Factura solicitada. Te la enviaremos al email ' . $request->email_factura . ' en las próximas 24 horas.');
    }

    // Método privado para notificar al admin
    private function notificarPagoAdmin($reserva, $pago)
    {
        // Aquí puedes implementar notificaciones al admin
        // Por ejemplo: Email, Telegram, Notificación en panel, etc.
        
        Log::info('PAGO REALIZADO - Notificar al admin', [
            'reserva_id' => $reserva->id,
            'cliente' => $reserva->cliente->nombre . ' ' . $reserva->cliente->apellido,
            'monto' => $pago->monto,
            'metodo' => $pago->metodo_pago,
            'referencia' => $pago->referencia,
            'habitacion' => $reserva->habitacion->tipo,
        ]);
        
        // También podrías guardar una notificación en la base de datos
        // \App\Models\Notificacion::create([...]);
    }
}