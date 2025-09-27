<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use Illuminate\Http\Request;

class HabitacionController extends Controller
{
    public function index()
    {
        $habitaciones = Habitacion::all();
        return view('habitaciones.index', compact('habitaciones'));
    }

    public function create()
    {
        return view('habitaciones.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|string|max:100',
            'descripcion' => 'required|string',
            'capacidad' => 'required|integer|min:1',
            'precio_noche' => 'required|numeric|min:0',
        ]);

        Habitacion::create($request->all());
        return redirect()->route('habitaciones.index')->with('success', 'Habitación creada exitosamente.');
    }

    public function edit($id)
    {
        $habitacion = Habitacion::findOrFail($id);
        return view('habitaciones.edit', compact('habitacion'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo' => 'required|string|max:100',
            'descripcion' => 'required|string',
            'capacidad' => 'required|integer|min:1',
            'precio_noche' => 'required|numeric|min:0',
        ]);

        $habitacion = Habitacion::findOrFail($id);
        $habitacion->update($request->all());
        return redirect()->route('habitaciones.index')->with('success', 'Habitación actualizada.');
    }

    public function destroy($id)
    {
        Habitacion::findOrFail($id)->delete();
        return redirect()->route('habitaciones.index')->with('success', 'Habitación eliminada.');
    }
}