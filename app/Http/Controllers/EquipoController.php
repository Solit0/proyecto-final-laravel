<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEquipoRequest;
use App\Models\Equipo;
use App\Models\Laboratorio;
use Illuminate\Http\RedirectResponse;
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
     * [CRUD-READ] Formulario de registro con laboratorios ordenados y equipo vacío.
     */
    public function create(): View
    {
        $laboratorios = Laboratorio::query()->orderBy('nombre')->get();

        return view('equipos.create', [
            'laboratorios' => $laboratorios,
            'equipo' => new Equipo(),
        ]);
    }

    /**
     * [CRUD-CREATE] [SEGURIDAD] [INYECCION-DEPENDENCIAS]
     * Registra un nuevo equipo usando únicamente datos validados y gestionando la fotografía.
     */
    public function store(StoreEquipoRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        if ($request->hasFile('imagen')) {
            $datos['imagen'] = $request->file('imagen')->store('equipos', 'public');
        }

        $equipo = Equipo::query()->create($datos);

        return redirect()
            ->route('equipos.show', $equipo)
            ->with('exito', 'Equipo registrado correctamente.');
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
