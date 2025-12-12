<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ClienteFacturaController extends Controller
{
    // Mostrar listado de facturas
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Obtener ventas facturadas del cliente
        $ventas = Venta::where('cliente_id', $user->cliente_id)
            ->where('facturada', true)
            ->with(['reserva', 'reserva.habitacion'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        // Estadísticas
        $estadisticas = [
            'total_facturado' => Venta::where('cliente_id', $user->cliente_id)
                ->where('facturada', true)
                ->sum('monto_total'),
            'facturas_pendientes' => Venta::where('cliente_id', $user->cliente_id)
                ->where('facturada', false)
                ->where('estado', 'completada')
                ->count(),
        ];
        
        return view('cliente.facturas.index', compact('ventas', 'estadisticas'));
    }

    // Mostrar detalles de factura
    public function show($id)
    {
        $venta = Venta::with(['reserva', 'reserva.habitacion', 'pagos'])
            ->where('facturada', true)
            ->findOrFail($id);
            
        $user = Auth::user();
        
        // Verificar que la factura pertenezca al cliente
        if ($venta->cliente_id != $user->cliente_id) {
            abort(403, 'No tienes permiso para ver esta factura.');
        }
        
        // Parsear datos de facturación
        $datosFacturacion = $venta->datos_facturacion ? json_decode($venta->datos_facturacion, true) : null;
        
        return view('cliente.facturas.show', compact('venta', 'datosFacturacion'));
    }

    // Descargar factura en PDF
    public function descargar(Request $request, $id)
    {
        $venta = Venta::with(['reserva', 'reserva.habitacion', 'reserva.cliente'])
            ->where('facturada', true)
            ->findOrFail($id);
            
        $user = Auth::user();
        
        // Verificar permisos
        if ($venta->cliente_id != $user->cliente_id) {
            abort(403, 'No tienes permiso para descargar esta factura.');
        }
        
        try {
            // Datos para la factura
            $datosFacturacion = $venta->datos_facturacion ? json_decode($venta->datos_facturacion, true) : [];
            
            $data = [
                'venta' => $venta,
                'cliente' => $venta->reserva->cliente,
                'reserva' => $venta->reserva,
                'habitacion' => $venta->reserva->habitacion,
                'datos_facturacion' => $datosFacturacion,
                'fecha_emision' => now()->format('d/m/Y'),
                'numero_factura' => 'FACT-' . str_pad($venta->id, 6, '0', STR_PAD_LEFT),
                'empresa' => [
                    'nombre' => 'Hotel ICI',
                    'rut' => '12.345.678-9',
                    'direccion' => 'Av. Siempre Viva 123, Ciudad',
                    'telefono' => '+56 9 1234 5678',
                    'email' => 'facturacion@hotelici.com',
                    'giro' => 'HOSPEDAJE Y SERVICIOS DE HOTELERÍA',
                ],
            ];
            
            // Generar PDF
            $pdf = PDF::loadView('cliente.facturas.pdf', $data);
            
            // Configurar papel
            $pdf->setPaper('A4', 'portrait');
            
            // Nombre del archivo
            $filename = 'factura-hotelici-' . $venta->id . '-' . now()->format('Ymd') . '.pdf';
            
            Log::info('Factura descargada', [
                'venta_id' => $venta->id,
                'cliente_id' => $user->cliente_id,
                'filename' => $filename,
            ]);
            
            // Descargar o ver en navegador
            if ($request->input('preview') == '1') {
                return $pdf->stream($filename);
            }
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            Log::error('Error generando factura PDF', [
                'error' => $e->getMessage(),
                'venta_id' => $id,
            ]);
            
            return back()->with('error', 'Error al generar la factura: ' . $e->getMessage());
        }
    }

    // Reenviar factura por email
    public function reenviar(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        
        $venta = Venta::with(['reserva', 'reserva.cliente'])
            ->where('facturada', true)
            ->findOrFail($id);
            
        $user = Auth::user();
        
        // Verificar permisos
        if ($venta->cliente_id != $user->cliente_id) {
            abort(403, 'No tienes permiso para reenviar esta factura.');
        }
        
        try {
            // Aquí normalmente enviarías el email con la factura adjunta
            // Por ahora solo simulamos el envío
            
            Log::info('Factura reenviada por email', [
                'venta_id' => $venta->id,
                'cliente_id' => $user->cliente_id,
                'email_destino' => $request->email,
                'email_original' => $venta->reserva->cliente->email,
            ]);
            
            // Actualizar datos de facturación con el nuevo email
            $datosFacturacion = $venta->datos_facturacion ? json_decode($venta->datos_facturacion, true) : [];
            $datosFacturacion['reenvios'][] = [
                'fecha' => now()->format('Y-m-d H:i:s'),
                'email' => $request->email,
                'solicitado_por' => $user->id,
            ];
            
            $venta->update([
                'datos_facturacion' => json_encode($datosFacturacion),
            ]);
            
            return back()->with('success', 'Factura reenviada a ' . $request->email);
            
        } catch (\Exception $e) {
            Log::error('Error reenviando factura', [
                'error' => $e->getMessage(),
                'venta_id' => $id,
            ]);
            
            return back()->with('error', 'Error al reenviar la factura: ' . $e->getMessage());
        }
    }
}