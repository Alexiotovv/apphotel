<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminReservaController extends Controller
{
    // Listar todas las reservas
    public function index(Request $request)
    {
        $query = Reserva::with(['cliente', 'habitacion', 'venta'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        if ($request->filled('cliente')) {
            $query->whereHas('cliente', function($q) use ($request) {
                $q->where('nombre', 'like', '%'.$request->cliente.'%')
                  ->orWhere('apellido', 'like', '%'.$request->cliente.'%')
                  ->orWhere('dni', 'like', '%'.$request->cliente.'%');
            });
        }

        $reservas = $query->paginate(15);

        // Estadísticas
        $stats = [
            'total' => Reserva::count(),
            'pendientes' => Reserva::where('estado', 'pendiente')->count(),
            'confirmadas' => Reserva::where('estado', 'confirmada')->count(),
            'canceladas' => Reserva::where('estado', 'cancelada')->count(),
            'ingresos_hoy' => Reserva::whereDate('created_at', today())
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->sum('precio_total'),
            'ingresos_mes' => Reserva::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->sum('precio_total'),
        ];

        return view('admin.reservas.index', compact('reservas', 'stats'));
    }

    // Mostrar detalles de una reserva
    public function show($id)
    {
        $reserva = Reserva::with(['cliente', 'habitacion', 'venta'])->findOrFail($id);
        return view('admin.reservas.show', compact('reserva'));
    }

    // Mostrar formulario de edición
    public function edit($id)
    {
        $reserva = Reserva::with(['cliente', 'habitacion'])->findOrFail($id);
        $habitaciones = Habitacion::where('disponible', true)->get();
        
        return view('admin.reservas.edit', compact('reserva', 'habitaciones'));
    }

    // Actualizar reserva
    public function update(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,confirmada,cancelada',
            'habitacion_id' => 'required|exists:habitaciones,id',
            'fecha_entrada' => 'required|date',
            'fecha_salida' => 'required|date|after:fecha_entrada',
            'adultos' => 'required|integer|min:1',
            'ninos' => 'integer|min:0',
            'precio_total' => 'required|numeric|min:0',
            'notas_admin' => 'nullable|string|max:1000',
        ]);

        $reserva = Reserva::findOrFail($id);
        
        // Calcular noches si cambian las fechas
        $fechaEntrada = Carbon::parse($request->fecha_entrada);
        $fechaSalida = Carbon::parse($request->fecha_salida);
        $noches = $fechaEntrada->diffInDays($fechaSalida);

        // Actualizar reserva
        $reserva->update([
            'estado' => $request->estado,
            'habitacion_id' => $request->habitacion_id,
            'fecha_entrada' => $request->fecha_entrada,
            'fecha_salida' => $request->fecha_salida,
            'noches' => $noches,
            'adultos' => $request->adultos,
            'ninos' => $request->ninos,
            'precio_total' => $request->precio_total,
            'notas_admin' => $request->notas_admin,
        ]);

        // Si se confirma la reserva, actualizar la venta
        if ($request->estado == 'confirmada' && $reserva->venta) {
            $reserva->venta->update([
                'estado' => 'completada',
                'monto_total' => $request->precio_total,
            ]);
        }

        // Si se cancela la reserva
        if ($request->estado == 'cancelada' && $reserva->venta) {
            $reserva->venta->update(['estado' => 'cancelada']);
        }

        return redirect()->route('admin.reservas.index')
            ->with('success', 'Reserva actualizada exitosamente.');
    }

    // Confirmar reserva
    public function confirmar($id)
    {
        $reserva = Reserva::findOrFail($id);
        
        $reserva->update([
            'estado' => 'confirmada',
        ]);

        if ($reserva->venta) {
            $reserva->venta->update(['estado' => 'completada']);
        }

        return back()->with('success', 'Reserva confirmada exitosamente.');
    }

    // Cancelar reserva
    public function cancelar($id)
    {
        $reserva = Reserva::findOrFail($id);
        
        $reserva->update([
            'estado' => 'cancelada',
        ]);

        if ($reserva->venta) {
            $reserva->venta->update(['estado' => 'cancelada']);
        }

        return back()->with('success', 'Reserva cancelada exitosamente.');
    }

    // Eliminar reserva
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $reserva = Reserva::findOrFail($id);
            
            // Eliminar venta asociada si existe
            if ($reserva->venta) {
                $reserva->venta->delete();
            }
            
            $reserva->delete();
            
            DB::commit();
            
            return redirect()->route('admin.reservas.index')
                ->with('success', 'Reserva eliminada exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar la reserva: ' . $e->getMessage());
        }
    }

    // Dashboard de reservas
    public function dashboard()
    {
        // Reservas por estado
        $reservasPorEstado = DB::table('reservas')
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();

        // Reservas por mes (últimos 6 meses)
        $reservasPorMes = Reserva::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'),
                DB::raw('count(*) as total'),
                DB::raw('sum(precio_total) as ingresos')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // Próximas reservas (próximos 7 días)
        $proximasReservas = Reserva::with(['cliente', 'habitacion'])
            ->whereDate('fecha_entrada', '>=', today())
            ->whereDate('fecha_entrada', '<=', today()->addDays(7))
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->orderBy('fecha_entrada')
            ->limit(10)
            ->get();

        // Habitaciones más reservadas
        $habitacionesPopulares = DB::table('reservas')
            ->join('habitaciones', 'reservas.habitacion_id', '=', 'habitaciones.id')
            ->select('habitaciones.tipo', DB::raw('count(*) as total_reservas'))
            ->groupBy('habitacion_id', 'habitaciones.tipo')
            ->orderByDesc('total_reservas')
            ->limit(5)
            ->get();

        return view('admin.reservas.dashboard', compact(
            'reservasPorEstado',
            'reservasPorMes',
            'proximasReservas',
            'habitacionesPopulares'
        ));
    }

    // Exportar reservas a Excel (requiere maatwebsite/excel)
    public function exportar(Request $request)
    {
        $reservas = Reserva::with(['cliente', 'habitacion'])
            ->when($request->filled('fecha_desde'), function($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->fecha_desde);
            })
            ->when($request->filled('fecha_hasta'), function($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->fecha_hasta);
            })
            ->when($request->filled('estado'), function($q) use ($request) {
                $q->where('estado', $request->estado);
            })
            ->get();

        // Si tienes instalado maatwebsite/excel:
        // return Excel::download(new ReservasExport($reservas), 'reservas.xlsx');
        
        // Versión simple CSV
        $csv = \League\Csv\Writer::createFromString('');
        $csv->insertOne(['ID', 'Cliente', 'DNI', 'Email', 'Teléfono', 'Habitación', 'Entrada', 'Salida', 'Noches', 'Total', 'Estado', 'Fecha Reserva']);

        foreach ($reservas as $reserva) {
            $csv->insertOne([
                $reserva->id,
                $reserva->cliente->nombre . ' ' . $reserva->cliente->apellido,
                $reserva->cliente->dni,
                $reserva->cliente->email,
                $reserva->cliente->telefono,
                $reserva->habitacion->tipo,
                $reserva->fecha_entrada->format('d/m/Y'),
                $reserva->fecha_salida->format('d/m/Y'),
                $reserva->noches,
                number_format($reserva->precio_total, 2),
                $reserva->estado,
                $reserva->created_at->format('d/m/Y H:i'),
            ]);
        }

        return response((string) $csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="reservas_' . date('Y-m-d') . '.csv"',
        ]);
    }
}