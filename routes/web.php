<?php

use App\Http\Controllers\EquipoController;
use App\Http\Controllers\MantenimientoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Contrato de recursos de Equipos gestionado por David.
Route::resource('equipos', EquipoController::class);

// Contrato de rutas del módulo de mantenimientos para integrar por David.
Route::get('/mantenimientos', [MantenimientoController::class, 'index'])->name('mantenimientos.index');
Route::get('/mantenimientos/create', [MantenimientoController::class, 'create'])->name('mantenimientos.create');
Route::post('/mantenimientos', [MantenimientoController::class, 'store'])->name('mantenimientos.store');
