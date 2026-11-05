<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EquipoController extends Controller
{
    /**
     * [CRUD-READ] Listado paginado de 10 en 10 ordenado por código de inventario,
     * precargando el laboratorio de cada equipo.
     */
    public function index(Request $request): View
    {
        $equipos = Equipo::query()
            ->with('laboratorio')
            ->orderBy('codigo_inventario')
            ->paginate(10);

        return view('equipos.index', [
            'equipos' => $equipos,
        ]);
    }

    /**
     * [CRUD-READ] Ficha de detalle con carga optimizada del laboratorio e historial
     * de mantenimientos ordenado descendentemente por fecha.
     */
    public function show(Equipo $equipo): View
    {
        $equipo->load([
            'laboratorio',
            'mantenimientos' => fn ($query) => $query->orderByDesc('fecha')->orderByDesc('id'),
        ]);

        return view('equipos.show', [
            'equipo' => $equipo,
        ]);
    }
}
