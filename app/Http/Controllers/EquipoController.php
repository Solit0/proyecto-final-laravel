<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEquipoRequest;
use App\Http\Requests\UpdateEquipoRequest;
use App\Models\Equipo;
use App\Models\Laboratorio;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use LogicException;

class EquipoController extends Controller
{
    /**
     * Listado paginado con búsqueda y filtros por laboratorio y estado.
     */
    public function index(Request $request): View
    {
        $query = Equipo::query()->with('laboratorio');

        if ($request->filled('q')) {
            $busqueda = '%'.mb_strtolower(trim((string) $request->input('q'))).'%';
            $query->where(function ($subQuery) use ($busqueda): void {
                $subQuery->whereRaw('LOWER(codigo_inventario) LIKE ?', [$busqueda])
                    ->orWhereRaw('LOWER(marca) LIKE ?', [$busqueda])
                    ->orWhereRaw('LOWER(modelo) LIKE ?', [$busqueda]);
            });
        }

        if ($request->filled('laboratorio_id')) {
            $query->where('laboratorio_id', $request->integer('laboratorio_id'));
        }

        if ($request->filled('estado') && in_array($request->input('estado'), Equipo::ESTADOS, true)) {
            $query->where('estado', $request->input('estado'));
        }

        $equipos = $query->orderBy('codigo_inventario')->paginate(10)->withQueryString();
        $laboratorios = Laboratorio::query()->orderBy('nombre')->get();

        return view('equipos.index', [
            'equipos' => $equipos,
            'laboratorios' => $laboratorios,
        ]);
    }

    /**
     * Formulario de registro de equipo.
     */
    public function create(): View
    {
        $laboratorios = Laboratorio::query()->orderBy('nombre')->get();

        return view('equipos.create', [
            'laboratorios' => $laboratorios,
            'equipo' => new Equipo,
        ]);
    }

    /**
     * Registra un nuevo equipo y gestiona su fotografía.
     */
    public function store(StoreEquipoRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $archivoNuevo = null;

        if ($request->hasFile('imagen')) {
            $archivoNuevo = $request->file('imagen')->store('equipos', 'public');
            $datos['imagen'] = $archivoNuevo;
        }

        try {
            $equipo = Equipo::query()->create($datos);
        } catch (\Throwable $e) {
            if ($archivoNuevo && Storage::disk('public')->exists($archivoNuevo)) {
                Storage::disk('public')->delete($archivoNuevo);
            }
            throw $e;
        }

        return redirect()
            ->route('equipos.show', $equipo)
            ->with('exito', 'Equipo registrado correctamente.');
    }

    /**
     * Detalle del equipo con historial de mantenimientos.
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
     * Formulario de edición del equipo.
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
     * Actualiza el equipo validando el historial y gestionando su imagen.
     */
    public function update(UpdateEquipoRequest $request, Equipo $equipo): RedirectResponse
    {
        $archivoNuevo = null;
        $archivoAnteriorParaBorrar = null;

        try {
            DB::transaction(function () use ($equipo, $request, &$archivoNuevo, &$archivoAnteriorParaBorrar): void {
                // Bloqueo pesimista para evitar colisiones con mantenimientos concurrentes
                $equipoBloqueado = Equipo::query()->lockForUpdate()->findOrFail($equipo->id);
                $datos = $request->validated();

                // La fecha de adquisición no puede ser posterior al mantenimiento más antiguo
                $mantenimientoMasAntiguo = $equipoBloqueado->mantenimientos()
                    ->orderBy('fecha')
                    ->orderBy('id')
                    ->first();

                if ($mantenimientoMasAntiguo !== null) {
                    $nuevaAdquisicion = Carbon::parse($datos['fecha_adquisicion'])->startOfDay();
                    if ($nuevaAdquisicion->gt($mantenimientoMasAntiguo->fecha)) {
                        throw ValidationException::withMessages([
                            'fecha_adquisicion' => 'La fecha de adquisición no puede ser posterior al mantenimiento más antiguo del equipo ('.$mantenimientoMasAntiguo->fecha->format('d/m/Y').').',
                        ]);
                    }
                }

                if ($request->hasFile('imagen')) {
                    $archivoNuevo = $request->file('imagen')->store('equipos', 'public');
                    $datos['imagen'] = $archivoNuevo;
                    $archivoAnteriorParaBorrar = $equipoBloqueado->imagen;
                } else {
                    unset($datos['imagen']);
                }

                $equipoBloqueado->update($datos);
            });

            if ($archivoAnteriorParaBorrar && Storage::disk('public')->exists($archivoAnteriorParaBorrar)) {
                Storage::disk('public')->delete($archivoAnteriorParaBorrar);
            }
        } catch (\Throwable $e) {
            // Revertir archivo nuevo si la transacción falla
            if ($archivoNuevo && Storage::disk('public')->exists($archivoNuevo)) {
                Storage::disk('public')->delete($archivoNuevo);
            }
            throw $e;
        }

        return redirect()
            ->route('equipos.show', $equipo)
            ->with('exito', 'Equipo actualizado correctamente.');
    }

    /**
     * Elimina el equipo si no posee historial de mantenimientos.
     */
    public function destroy(Equipo $equipo): RedirectResponse
    {
        try {
            $archivoABorrar = null;

            DB::transaction(function () use ($equipo, &$archivoABorrar): void {
                // Bloqueo de fila para verificar historial de forma atómica
                $equipoBloqueado = Equipo::query()->lockForUpdate()->findOrFail($equipo->id);

                if ($equipoBloqueado->mantenimientos()->exists()) {
                    throw new LogicException('tiene_historial');
                }

                $archivoABorrar = $equipoBloqueado->imagen;
                $equipoBloqueado->delete();
            });

            if ($archivoABorrar && Storage::disk('public')->exists($archivoABorrar)) {
                Storage::disk('public')->delete($archivoABorrar);
            }
        } catch (LogicException|QueryException) {
            return redirect()
                ->route('equipos.index')
                ->with('error', 'No se puede eliminar porque tiene historial; puede marcarse de baja.');
        }

        return redirect()
            ->route('equipos.index')
            ->with('exito', 'Equipo eliminado correctamente.');
    }
}
