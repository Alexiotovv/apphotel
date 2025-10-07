<?php

// Indica que este archivo pertenece al espacio de nombres (namespace) de los controladores de la aplicación
namespace App\Http\Controllers;

// Importa el modelo Habitacion para poder usarlo en este controlador
use App\Models\Habitacion;

// Importa la clase Request para manejar los datos que vienen de formularios o peticiones HTTP
use Illuminate\Http\Request;

// Define una clase llamada HabitacionController que controla las acciones relacionadas con las habitaciones
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

    // Guarda una nueva habitación en la base de datos
    public function store(Request $request)
    {
        // Valida que los campos del formulario tengan datos correctos antes de guardar
        $request->validate([
            'tipo' => 'required|string|max:100',        // El tipo es obligatorio, texto y máximo 100 caracteres
            'descripcion' => 'required|string',         // La descripción es obligatoria y debe ser texto
            'capacidad' => 'required|integer|min:1',    // La capacidad debe ser número entero mayor o igual a 1
            'precio_noche' => 'required|numeric|min:0', // El precio debe ser número mayor o igual a 0
        ]);

        // Crea una nueva habitación con los datos validados del formulario
        Habitacion::create($request->all());
        
        // Redirige al listado de habitaciones con un mensaje de éxito
        return redirect()->route('habitaciones.index')->with('success', 'Habitación creada exitosamente.');
    }

    // Muestra el formulario para editar una habitación existente
    public function edit($id)
    {
        // Busca la habitación por su ID o muestra error si no existe
        $habitacion = Habitacion::findOrFail($id);
        
        // Envía los datos de la habitación a la vista de edición
        return view('habitaciones.edit', compact('habitacion'));
    }

    // Actualiza los datos de una habitación existente
    public function update(Request $request, $id)
    {
        // Valida los campos del formulario igual que en la creación
        $request->validate([
            'tipo' => 'required|string|max:100',
            'descripcion' => 'required|string',
            'capacidad' => 'required|integer|min:1',
            'precio_noche' => 'required|numeric|min:0',
        ]);

        // Busca la habitación a modificar
        $habitacion = Habitacion::findOrFail($id);
        
        // Actualiza sus datos con la información del formulario
        $habitacion->update($request->all());
        
        // Redirige al listado con un mensaje de confirmación
        return redirect()->route('habitaciones.index')->with('success', 'Habitación actualizada.');
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
