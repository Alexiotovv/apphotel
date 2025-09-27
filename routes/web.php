<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HabitacionController;

// Página pública (con chatbot)
Route::get('/', function () {
    return view('public.index');
})->name('home');

// Autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rutas protegidas (solo admin)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/habitaciones', [HabitacionController::class, 'index'])->name('habitaciones.index');
    Route::get('/admin/habitaciones/create', [HabitacionController::class, 'create'])->name('habitaciones.create');
    Route::post('/admin/habitaciones', [HabitacionController::class, 'store']);
    Route::get('/admin/habitaciones/{id}/edit', [HabitacionController::class, 'edit'])->name('habitaciones.edit');
    Route::put('/admin/habitaciones/{id}', [HabitacionController::class, 'update']);
    Route::delete('/admin/habitaciones/{id}', [HabitacionController::class, 'destroy']);
});