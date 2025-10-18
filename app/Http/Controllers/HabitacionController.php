<?php

// Indica que este archivo pertenece al espacio de nombres (namespace) de los controladores de la aplicación
namespace App\Http\Controllers;

// Importa el modelo Habitacion para poder usarlo en este controlador
use App\Models\Habitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HabitacionController extends Controller
{
    // Muestra la lista de habitaciones registradas
    public function index()
    {
        // Obtiene todas las habitaciones de la base de datos
        $habitaciones = Habitacion::all();
        
        // Envía los datos a la vista 'habitaciones.index' para mostrarlos en pantalla
        return view('habitaciones.index', compact('habitaciones'));
    }

    // Muestra el formulario para crear una nueva habitación
    public function create()
    {
        // Devuelve la vista donde el usuario puede llenar los datos de una nueva habitación
        return view('habitaciones.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|string|max:100',
            'descripcion' => 'required|string',
            'capacidad' => 'required|integer|min:1',
            'precio_noche' => 'required|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $data = $request->only(['tipo', 'descripcion', 'capacidad', 'precio_noche']);

        if ($request->hasFile('foto')) {
            // Guarda el archivo en la carpeta 'habitaciones' del disco 'public'
            $path = $request->file('foto')->store('habitaciones', 'public');
            
            // Extrae solo el nombre del archivo (última parte de la ruta)
            $filename = basename($path);
            
            $data['foto'] = $filename;
        }

        Habitacion::create($data);

        return redirect()->route('habitaciones.index')->with('success', 'Habitación creada exitosamente.');
    }
    // Actualiza los datos de una habitación existente
    public function update(Request $request, Habitacion $habitacion)
    {
        $request->validate([
            'tipo' => 'required|string|max:100',
            'descripcion' => 'required|string',
            'capacidad' => 'required|integer|min:1',
            'precio_noche' => 'required|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $data = $request->only(['tipo', 'descripcion', 'capacidad', 'precio_noche']);

        if ($request->hasFile('foto')) {
            // 1. Eliminar la foto anterior si existe
            if ($habitacion->foto) {
                $oldPath = 'habitaciones/' . $habitacion->foto;
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // 2. Guardar la nueva foto y solo almacenar el nombre
            $path = $request->file('foto')->store('habitaciones', 'public');
            $data['foto'] = basename($path);
        }

        $habitacion->update($data);

        return redirect()->route('habitaciones.index')->with('success', 'Habitación actualizada exitosamente.');
    }




    
    // Muestra el formulario para editar una habitación existente
    public function edit($id)
    {
        // Busca la habitación por su ID o muestra error si no existe
        $habitacion = Habitacion::findOrFail($id);
        
        // Envía los datos de la habitación a la vista de edición
        return view('habitaciones.edit', compact('habitacion'));
    }

    

    // Elimina una habitación de la base de datos
    public function destroy($id)
    {
        // Busca la habitación por ID y la elimina
        Habitacion::findOrFail($id)->delete();
        
        // Redirige al listado mostrando un mensaje de eliminación
        return redirect()->route('habitaciones.index')->with('success', 'Habitación eliminada.');
    }
}
