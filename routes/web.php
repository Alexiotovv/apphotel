<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\ExternalChatbotController;
use App\Http\Controllers\PaginaPrincipalController;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index']);

// Autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::post('/chatbot', [ExternalChatbotController::class, 'respond']);
// Rutas protegidas (solo admin)
Route::middleware(['auth', 'admin'])->group(function () {
    
    Route::put('/admin/portada', [PaginaPrincipalController::class, 'update'])->name('portada.update');
    Route::get('/admin/portada/edit', [PaginaPrincipalController::class, 'edit'])->name('portada.edit');

    Route::get('/admin/habitaciones', [HabitacionController::class, 'index'])->name('habitaciones.index');
    Route::get('/admin/habitaciones/create', [HabitacionController::class, 'create'])->name('habitaciones.create');
    Route::post('/admin/habitaciones', [HabitacionController::class, 'store'])->name('habitaciones.store');
    Route::get('/admin/habitaciones/{habitacion}/edit', [HabitacionController::class, 'edit'])->name('habitaciones.edit');
    Route::put('/admin/habitaciones/{habitacion}', [HabitacionController::class, 'update'])->name('habitaciones.update');
    Route::delete('/admin/habitaciones/{habitacion}', [HabitacionController::class, 'destroy'])->name('habitaciones.destroy');
    Route::resource('/admin/servicios', ServicioController::class);
});

