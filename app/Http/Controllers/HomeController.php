<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use Illuminate\Http\Request;
use App\Models\PaginaPrincipal;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function index()
    {
        $habitaciones = Habitacion::where('disponible', true)->get();
        $portada = PaginaPrincipal::first();

        return view('public.index', compact('habitaciones','portada'));
    }
}