<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Ruta de bienvenida (redirige al login o dashboard)
Route::get('/', function () {
    return auth()->check() 
        ? redirect()->route('dashboard') 
        : redirect()->route('login');
});

// Rutas públicas de autenticación
Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Webhook PagoFácil (ruta pública)
Route::post('/api/pagofacil/callback', [App\Http\Controllers\PagoController::class, 'webhook'])
    ->name('pagofacil.webhook');

// Rutas protegidas (requieren autenticación)
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // CU1 - Gestión de Usuarios
    Route::middleware(['role:SECRETARIA,PROPIETARIO'])->group(function () {
        Route::resource('usuarios', App\Http\Controllers\UsuarioController::class);
    });
    
    // CU2 - Gestión de Vehículos
    Route::middleware(['role:SECRETARIA,PROPIETARIO,CHOFER'])->group(function () {
        Route::resource('vehiculos', App\Http\Controllers\VehiculoController::class);
        Route::post('vehiculos/{vehiculo}/gps', [App\Http\Controllers\VehiculoController::class, 'actualizarGPS'])
            ->name('vehiculos.gps');
    });
    
    // CU3 - Gestión de Destinos
    Route::middleware(['role:SECRETARIA,PROPIETARIO'])->group(function () {
        Route::resource('destinos', App\Http\Controllers\DestinoController::class);
    });
    
    // CU4 - Gestión de Rutas
    Route::middleware(['role:SECRETARIA,PROPIETARIO'])->group(function () {
        Route::resource('rutas', App\Http\Controllers\RutaController::class);
    });
    
    // CU5 - Gestión de Paquetes
    Route::resource('paquetes', App\Http\Controllers\PaqueteController::class);
    Route::post('paquetes/{paquete}/entregar', [App\Http\Controllers\PaqueteController::class, 'entregar'])
        ->name('paquetes.entregar')
        ->middleware(['role:CHOFER,SECRETARIA,PROPIETARIO']);
    
    // CU6 - Seguimiento (público para rastreo)
    Route::get('/rastreo', [App\Http\Controllers\SeguimientoController::class, 'rastreo'])->name('rastreo');
    Route::get('/rastreo/{codigo}', [App\Http\Controllers\SeguimientoController::class, 'mostrar'])->name('rastreo.mostrar');
    
    // CU7 - Gestión de Pagos con PagoFácil y Cuotas
    Route::get('pagos', [App\Http\Controllers\PagoController::class, 'index'])
        ->name('pagos.index');
    Route::get('paquetes/{id_paquete}/pagar', [App\Http\Controllers\PagoController::class, 'create'])
        ->name('pagos.create');
    Route::post('pagos', [App\Http\Controllers\PagoController::class, 'store'])
        ->name('pagos.store');
    Route::get('/pagos/{pago}/cuotas', [App\Http\Controllers\PagoController::class, 'verCuotas'])->name('pagos.cuotas');
    Route::post('/pagos/cuotas/{cuota}/pagar-efectivo', [App\Http\Controllers\PagoController::class, 'pagarCuotaEfectivo'])->name('pagos.cuotas.pagar-efectivo');
    Route::post('api/cuotas/{id_cuota}/pagar', [App\Http\Controllers\PagoController::class, 'pagarCuota'])
        ->name('cuotas.pagar');
    Route::post('api/pagos/{id_paquete}/generar-qr', [App\Http\Controllers\PagoController::class, 'generarQR'])
        ->name('pagos.generar-qr');
    Route::get('api/pagos/{id_pago}/estado', [App\Http\Controllers\PagoController::class, 'consultarEstado'])
        ->name('pagos.consultar-estado');
    
    // CU8 - Reportes
    Route::middleware(['role:SECRETARIA,PROPIETARIO'])->group(function () {
        Route::get('/reportes', [App\Http\Controllers\ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/pdf', [App\Http\Controllers\ReporteController::class, 'exportarPdf'])->name('reportes.pdf');
        Route::get('/reportes/diario', [App\Http\Controllers\ReporteController::class, 'diario'])->name('reportes.diario');
        Route::get('/reportes/clientes', [App\Http\Controllers\ReporteController::class, 'clientes'])->name('reportes.clientes');
        Route::get('/reportes/vehiculos', [App\Http\Controllers\ReporteController::class, 'vehiculos'])->name('reportes.vehiculos');
        Route::get('/reportes/rutas', [App\Http\Controllers\ReporteController::class, 'rutas'])->name('reportes.rutas');
    });
    
    // CU9 - Rastreo de Paquetes (público para clientes)
    Route::get('/rastreo', function () {
        return Inertia::render('Rastreo/Index');
    })->name('rastreo.index');
    Route::get('/rastreo/{codigo}', [App\Http\Controllers\RastreoController::class, 'show'])->name('rastreo.show');
    
    // GPS - Rastreo en tiempo real
    Route::middleware(['auth'])->group(function () {
        Route::post('/api/gps/iniciar', [App\Http\Controllers\GpsController::class, 'iniciarRastreo'])->name('gps.iniciar');
        Route::post('/api/gps/actualizar', [App\Http\Controllers\GpsController::class, 'actualizarUbicacion'])->name('gps.actualizar');
        Route::post('/api/gps/finalizar', [App\Http\Controllers\GpsController::class, 'finalizarRastreo'])->name('gps.finalizar');
        Route::get('/api/gps/estado', [App\Http\Controllers\GpsController::class, 'obtenerEstado'])->name('gps.estado');
    });
    Route::get('/api/gps/vehiculo/{id}', [App\Http\Controllers\GpsController::class, 'obtenerUbicacion'])->name('gps.ubicacion');
    
    // Búsqueda global
    Route::get('/search', [App\Http\Controllers\SearchController::class, 'search'])->name('search');
});

// Autenticación (Laravel Breeze o manual)
require __DIR__.'/auth.php';
