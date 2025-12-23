<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\ExternalChatbotController;
use App\Http\Controllers\PaginaPrincipalController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\AdminReservaController;
use App\Http\Controllers\Cliente\ClienteReservaController;
use App\Http\Controllers\Cliente\ClientePerfilController;
use App\Http\Controllers\Cliente\ClientePagoController;
use App\Http\Controllers\Cliente\ClienteFacturaController;
use App\Http\Controllers\Auth\RegisterController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
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
    

    //Solo admin ver reservas
    Route::prefix('admin')->name('admin.')->group(function () {
        // Reservas
        Route::get('/reservas', [AdminReservaController::class, 'index'])->name('reservas.index');
        Route::get('/reservas/dashboard', [AdminReservaController::class, 'dashboard'])->name('reservas.dashboard');
        Route::get('/reservas/{id}', [AdminReservaController::class, 'show'])->name('reservas.show');
        Route::get('/reservas/{id}/edit', [AdminReservaController::class, 'edit'])->name('reservas.edit');
        Route::put('/reservas/{id}', [AdminReservaController::class, 'update'])->name('reservas.update');
        Route::post('/reservas/{id}/confirmar', [AdminReservaController::class, 'confirmar'])->name('reservas.confirmar');
        Route::post('/reservas/{id}/cancelar', [AdminReservaController::class, 'cancelar'])->name('reservas.cancelar');
        Route::delete('/reservas/{id}', [AdminReservaController::class, 'destroy'])->name('reservas.destroy');
        Route::get('/reservas/exportar', [AdminReservaController::class, 'exportar'])->name('reservas.exportar');
    });

});

// Rutas para clientes autenticados
Route::middleware(['auth'])->group(function () {  // Quita 'cliente' temporalmente
    Route::prefix('cliente')->name('cliente.')->group(function () {
        // Reservas del cliente
        Route::get('/reservas', [ClienteReservaController::class, 'index'])->name('reservas.index');
        Route::get('/reservas/{id}', [ClienteReservaController::class, 'show'])->name('reservas.show');
        Route::get('/reservas/{id}/pagar', [ClienteReservaController::class, 'pagar'])->name('reservas.pagar');
        Route::post('/reservas/{id}/pagar/tarjeta', [ClienteReservaController::class, 'procesarPagoTarjeta'])->name('reservas.pagar.tarjeta');
        Route::post('/reservas/{id}/pagar/qr', [ClienteReservaController::class, 'procesarPagoQR'])->name('reservas.pagar.qr');
        Route::get('/reservas/pago/{id}/qr', [ClienteReservaController::class, 'mostrarQR'])->name('reservas.qr');
        Route::get('/reservas/pago/{id}/verificar', [ClienteReservaController::class, 'verificarQR'])->name('reservas.verificar.qr');
        Route::get('/reservas/comprobante/{id}', [ClienteReservaController::class, 'comprobante'])->name('reservas.comprobante');
        Route::post('/reservas/{id}/cancelar', [ClienteReservaController::class, 'cancelar'])->name('reservas.cancelar');
        Route::post('/reservas/{id}/factura', [ClienteReservaController::class, 'solicitarFactura'])->name('reservas.factura');

        // Nuevas rutas
        Route::get('/perfil', [ClientePerfilController::class, 'show'])->name('perfil.show');
        Route::get('/perfil/edit', [ClientePerfilController::class, 'edit'])->name('perfil.edit');
        Route::put('/perfil', [ClientePerfilController::class, 'update'])->name('perfil.update');
        Route::put('/perfil/password', [ClientePerfilController::class, 'updatePassword'])->name('perfil.password');
        
        Route::get('/pagos', [ClientePagoController::class, 'index'])->name('pagos.index');
        Route::get('/pagos/{id}', [ClientePagoController::class, 'show'])->name('pagos.show');
        Route::post('/pagos/{id}/reembolso', [ClientePagoController::class, 'solicitarReembolso'])->name('pagos.reembolso');
        
        Route::get('/facturas', [ClienteFacturaController::class, 'index'])->name('facturas.index');
        Route::get('/facturas/{id}', [ClienteFacturaController::class, 'show'])->name('facturas.show');
        Route::get('/facturas/{id}/descargar', [ClienteFacturaController::class, 'descargar'])->name('facturas.descargar');
        Route::post('/facturas/{id}/reenviar', [ClienteFacturaController::class, 'reenviar'])->name('facturas.reenviar');



    });
});


// RUTAS DE RESERVA - SOLO PARA USUARIOS AUTENTICADOS
Route::middleware(['auth'])->group(function () {
    Route::get('/reservar', [ReservaController::class, 'create'])->name('reservas.create');
    Route::get('/reservar/habitacion/{habitacion}', [ReservaController::class, 'create'])->name('reservas.create.habitacion');
    Route::post('/reservar', [ReservaController::class, 'store'])->name('reservas.store');
    Route::get('/reservas/gracias/{id}', [ReservaController::class, 'gracias'])->name('reservas.gracias');
});
Route::get('language/{locale}', function ($locale) {
    if (array_key_exists($locale, config('app.available_locales'))) {
        App::setLocale($locale);
        Session::put('locale', $locale);
    }
    return redirect()->back();
})->name('language.switch');