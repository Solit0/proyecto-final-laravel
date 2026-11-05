<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEquipoRequest;
use App\Http\Requests\UpdateEquipoRequest;
use App\Models\Equipo;
use App\Models\Laboratorio;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
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

    /**
     * [CRUD-READ] Formulario de edición con el equipo cargado y laboratorios ordenados.
     */
    public function edit(Equipo $equipo): View
    {
        $laboratorios = Laboratorio::query()->orderBy('nombre')->get();

        return view('equipos.edit', [
            'equipo' => $equipo,
            'laboratorios' => $laboratorios,
        ]);
    }

    /**
     * [CRUD-UPDATE] [SEGURIDAD] [REGLA-NEGOCIO]
     * Actualiza el equipo dentro de una transacción con bloqueo de fila.
     * Verifica que la nueva adquisición no sea posterior al mantenimiento más antiguo
     * y preserva la fotografía actual si no se adjunta un nuevo archivo.
     */
    public function update(UpdateEquipoRequest $request, Equipo $equipo): RedirectResponse
    {
        DB::transaction(function () use ($equipo, $request): void {
            $equipoBloqueado = Equipo::query()->lockForUpdate()->findOrFail($equipo->id);
            $datos = $request->validated();

            // [REGLA-NEGOCIO] La fecha de adquisición no puede ser posterior al mantenimiento más antiguo
            $mantenimientoMasAntiguo = $equipoBloqueado->mantenimientos()
                ->orderBy('fecha')
                ->orderBy('id')
                ->first();

            if ($mantenimientoMasAntiguo !== null) {
                $nuevaAdquisicion = Carbon::parse($datos['fecha_adquisicion'])->startOfDay();
                if ($nuevaAdquisicion->gt($mantenimientoMasAntiguo->fecha)) {
                    throw ValidationException::withMessages([
                        'fecha_adquisicion' => 'La fecha de adquisición no puede ser posterior al mantenimiento más antiguo del equipo (' . $mantenimientoMasAntiguo->fecha->format('d/m/Y') . ').',
                    ]);
                }
            }

            if ($request->hasFile('imagen')) {
                $rutaAnterior = $equipoBloqueado->imagen;
                $datos['imagen'] = $request->file('imagen')->store('equipos', 'public');
                if ($rutaAnterior && Storage::disk('public')->exists($rutaAnterior)) {
                    Storage::disk('public')->delete($rutaAnterior);
                }
            } else {
                // Conservar la imagen actual si no se adjuntó una nueva
                unset($datos['imagen']);
            }

            $equipoBloqueado->update($datos);
        });

        return redirect()
            ->route('equipos.show', $equipo)
            ->with('exito', 'Equipo actualizado correctamente.');
    }
}
