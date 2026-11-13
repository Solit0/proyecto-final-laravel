<?php

use App\Http\Controllers\EquipoController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\PanelController;
use Illuminate\Support\Facades\Route;

// Panel y reportes
Route::get('/', [PanelController::class, 'index'])->name('panel.index');
Route::get('/reportes', [PanelController::class, 'reporte'])->name('reportes.index');

// Equipos
Route::resource('equipos', EquipoController::class);

// Mantenimientos
Route::get('/mantenimientos', [MantenimientoController::class, 'index'])->name('mantenimientos.index');
Route::get('/mantenimientos/create', [MantenimientoController::class, 'create'])->name('mantenimientos.create');
Route::post('/mantenimientos', [MantenimientoController::class, 'store'])->name('mantenimientos.store');
