<?php

use App\Http\Controllers\EquipoController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\PanelController;
use Illuminate\Support\Facades\Route;

// Panel de inicio y reportes generales gestionados por Harry.
Route::get('/', [PanelController::class, 'index'])->name('panel.index');
Route::get('/reportes', [PanelController::class, 'reporte'])->name('reportes.index');

// Contrato de recursos de Equipos gestionado por David.
Route::resource('equipos', EquipoController::class);

// Contrato de rutas del módulo de mantenimientos para integrar por David.
Route::get('/mantenimientos', [MantenimientoController::class, 'index'])->name('mantenimientos.index');
Route::get('/mantenimientos/create', [MantenimientoController::class, 'create'])->name('mantenimientos.create');
Route::post('/mantenimientos', [MantenimientoController::class, 'store'])->name('mantenimientos.store');
