<?php

namespace App\Http\Controllers;

use App\Models\PaginaPrincipal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaginaPrincipalController extends Controller
{

    public function update(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $pagina = PaginaPrincipal::first();

        $data = $request->only(['titulo', 'descripcion']);

        if ($request->hasFile('foto')) {
            // Eliminar foto anterior si existe
            if ($pagina->foto) {
                Storage::disk('public')->delete('portada/' . $pagina->foto);
            }
            // Guardar nueva foto y solo almacenar el nombre
            $path = $request->file('foto')->store('portada', 'public');
            $data['foto'] = basename($path);
        }

        $pagina->update($data);

        return redirect()->back()->with('success', 'Portada actualizada exitosamente.');
    }

     public function edit()
    {
        $pagina = PaginaPrincipal::first();
        // Si por alguna razón no existe el registro (aunque la migración lo crea), lo creamos
        if (!$pagina) {
            $pagina = PaginaPrincipal::create([
                'titulo' => 'Bienvenido al Hotel ICI',
                'descripcion' => 'Lujo, confort y atención personalizada en el corazón de la ciudad.',
            ]);
        }
        return view('paginaprincipal.edit', compact('pagina'));
    }




}