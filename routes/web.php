<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ControlEscolarController;

// Ruta Principal (Pantalla de inicio del sistema SICA)
Route::get('/', function () {
    return view('sica.index');
});

Route::get('/login', function () {
    return view('sica.dashboard');
})->name('login');

Route::get('/lector-fijo', function () {
    return view('sica.lector');
})->name('lector');

Route::get('/control-escolar', function () {
    return view('sica.control_escolar');
})->name('control.escolar');

// Sincronización general (localStorage -> MySQL)
Route::post('/api/sica/sync', [ControlEscolarController::class, 'sync'])->name('sica.sync');

// Plantilla oficial de credenciales (frente / reverso)
Route::post('/api/sica/plantilla-oficial/{lado}', [ControlEscolarController::class, 'plantillaOficial'])
    ->whereIn('lado', ['frente', 'reverso'])
    ->name('sica.plantilla');