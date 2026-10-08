<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ControlEscolarController;
use App\Models\Orientador;
use Illuminate\Support\Facades\Route;

// ===== Públicas =====

// Pantalla de inicio / login del sistema SICA
Route::get('/', function () {
    return view('sica.index');
});

Route::get('/login', function () {
    return view('sica.dashboard');
})->name('login');

// Autenticación
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');

// Solo nombres de orientadores, para llenar la lista de la pantalla de login.
// No incluye alumnos, CURP ni asistencias.
Route::get('/api/sica/orientadores', function () {
    return Orientador::orderBy('nombre')->get()
        ->map(fn ($o) => ['id' => (string) $o->id, 'nombre' => $o->nombre])
        ->values();
})->middleware('throttle:30,1')->name('sica.orientadores');

// ===== Requieren sesión iniciada =====
Route::middleware('auth')->group(function () {

    Route::get('/lector-fijo', function () {
        return view('sica.lector');
    })->name('lector');

    Route::get('/control-escolar', function () {
        return view('sica.control_escolar');
    })->name('control.escolar');

    // Estado completo del servidor (lo descargan computadoras y teléfonos)
    Route::get('/api/sica/estado', [ControlEscolarController::class, 'estado'])->name('sica.estado');

    // Sincronización general (localStorage -> MySQL) y escaneos del teléfono.
    // El guardia revisa el rol y el permiso según la acción.
    Route::post('/api/sica/sync', [ControlEscolarController::class, 'sync'])
        ->middleware('sica:sync')
        ->name('sica.sync');

    // Plantilla oficial de credenciales (frente / reverso): solo Control Escolar
    Route::post('/api/sica/plantilla-oficial/{lado}', [ControlEscolarController::class, 'plantillaOficial'])
        ->whereIn('lado', ['frente', 'reverso'])
        ->middleware('sica:control')
        ->name('sica.plantilla');
});