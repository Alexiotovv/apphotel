<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Venta;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientePagoController extends Controller
{
    // Mostrar historial de pagos
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Obtener todas las ventas del cliente
        $ventas = Venta::where('cliente_id', $user->cliente_id)
            ->with(['pagos', 'reserva'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        // Estadísticas
        $estadisticas = [
            'total_pagado' => Pago::whereHas('venta', function($q) use ($user) {
                    $q->where('cliente_id', $user->cliente_id);
                })
                ->where('estado', 'completado')
                ->sum('monto'),
            'pagos_completados' => Pago::whereHas('venta', function($q) use ($user) {
                    $q->where('cliente_id', $user->cliente_id);
                })
                ->where('estado', 'completado')
                ->count(),
            'pagos_pendientes' => Pago::whereHas('venta', function($q) use ($user) {
                    $q->where('cliente_id', $user->cliente_id);
                })
                ->where('estado', 'pendiente')
                ->count(),
        ];
        
        // Filtrar por fecha si se solicita
        if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
            $ventas = Venta::where('cliente_id', $user->cliente_id)
                ->whereBetween('created_at', [$request->fecha_desde, $request->fecha_hasta])
                ->with(['pagos', 'reserva'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }
        
        return view('cliente.pagos.index', compact('ventas', 'estadisticas'));
    }

    // Mostrar detalles de un pago
    public function show($id)
    {
        $pago = Pago::with(['venta', 'venta.reserva', 'venta.reserva.habitacion'])
            ->findOrFail($id);
            
        $user = Auth::user();
        
        // Verificar que el pago pertenezca al cliente
        if ($pago->venta->cliente_id != $user->cliente_id) {
            abort(403, 'No tienes permiso para ver este pago.');
        }
        
        return view('cliente.pagos.show', compact('pago'));
    }

    // Solicitar reembolso
    public function solicitarReembolso(Request $request, $id)
    {
        $request->validate([
            'motivo' => 'required|string|max:500',
        ]);
        
        $pago = Pago::with(['venta'])->findOrFail($id);
        $user = Auth::user();
        
        // Verificar que el pago pertenezca al cliente
        if ($pago->venta->cliente_id != $user->cliente_id) {
            abort(403, 'No tienes permiso para solicitar reembolso.');
        }
        
        // Verificar que el pago esté completado
        if ($pago->estado != 'completado') {
            return back()->with('error', 'Solo se pueden solicitar reembolsos de pagos completados.');
        }
        
        // Verificar que no haya pasado mucho tiempo (ej: 30 días)
        $diasTranscurridos = $pago->created_at->diffInDays(now());
        if ($diasTranscurridos > 30) {
            return back()->with('error', 'El plazo para solicitar reembolso ha expirado (30 días).');
        }
        
        try {
            // Actualizar estado del pago
            $pago->update([
                'estado' => 'reembolsado',
                'detalles' => json_encode(array_merge(
                    json_decode($pago->detalles, true) ?? [],
                    [
                        'solicitud_reembolso' => [
                            'fecha' => now()->format('Y-m-d H:i:s'),
                            'motivo' => $request->motivo,
                            'solicitado_por' => $user->id,
                        ]
                    ]
                )),
            ]);
            
            // Actualizar venta asociada
            $pago->venta->update([
                'estado' => 'cancelada',
            ]);
            
            // Actualizar reserva asociada
            if ($pago->venta->reserva) {
                $pago->venta->reserva->update([
                    'estado' => 'cancelada',
                    'notas' => ($pago->venta->reserva->notas ? $pago->venta->reserva->notas . "\n" : '') . 
                              "[REEMBOLSO SOLICITADO] " . $request->motivo,
                ]);
            }
            
            Log::info('Reembolso solicitado', [
                'pago_id' => $pago->id,
                'cliente_id' => $user->cliente_id,
                'motivo' => $request->motivo,
            ]);
            
            return redirect()->route('cliente.pagos.show', $pago->id)
                ->with('success', 'Solicitud de reembolso enviada. Te contactaremos en 24-48 horas.');
                
        } catch (\Exception $e) {
            Log::error('Error solicitando reembolso', [
                'error' => $e->getMessage(),
                'pago_id' => $id,
            ]);
            
            return back()->with('error', 'Error al solicitar reembolso: ' . $e->getMessage());
        }
    }
}