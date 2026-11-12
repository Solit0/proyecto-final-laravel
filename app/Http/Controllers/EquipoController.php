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
     * [CRUD-READ] Listado paginado de 10 en 10 ordenado por código de inventario,
     * con búsqueda insensible a mayúsculas y filtros acumulativos por laboratorio y estado.
     */
    public function index(Request $request): View
    {
        $query = Equipo::query()->with('laboratorio');

        // Búsqueda por q sobre código, marca o modelo agrupando los OR
        if ($request->filled('q')) {
            $busqueda = '%'.mb_strtolower(trim((string) $request->input('q'))).'%';
            $query->where(function ($subQuery) use ($busqueda): void {
                $subQuery->whereRaw('LOWER(codigo_inventario) LIKE ?', [$busqueda])
                    ->orWhereRaw('LOWER(marca) LIKE ?', [$busqueda])
                    ->orWhereRaw('LOWER(modelo) LIKE ?', [$busqueda]);
            });
        }

        // Filtro por laboratorio
        if ($request->filled('laboratorio_id')) {
            $query->where('laboratorio_id', $request->integer('laboratorio_id'));
        }

        // Filtro por estado válido
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
     * [CRUD-READ] Formulario de registro con catálogo de laboratorios ordenados alfabéticamente.
     * Entrega una instancia vacía de Equipo para el enlace bidireccional del formulario.
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
     * [CRUD-CREATE] [SEGURIDAD] [INYECCION-DEPENDENCIAS] [VALIDACION] [REGLA-NEGOCIO]
     * Registra un nuevo equipo usando únicamente datos validados y gestionando la fotografía.
     * En caso de fallo en la base de datos, compensa eliminando el archivo recién subido.
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
     * [CRUD-UPDATE] [SEGURIDAD] [REGLA-NEGOCIO] [VALIDACION] [INYECCION-DEPENDENCIAS]
     * Actualiza el equipo dentro de una transacción con bloqueo de fila.
     * Verifica que la nueva adquisición no sea posterior al mantenimiento más antiguo,
     * preserva la fotografía actual si no se adjunta un nuevo archivo,
     * y asegura que el archivo anterior solo se borre si la base de datos confirmó el cambio.
     */
    public function update(UpdateEquipoRequest $request, Equipo $equipo): RedirectResponse
    {
        $archivoNuevo = null;
        $archivoAnteriorParaBorrar = null;

        try {
            DB::transaction(function () use ($equipo, $request, &$archivoNuevo, &$archivoAnteriorParaBorrar): void {
                // [SEGURIDAD] [REGLA-NEGOCIO] Bloqueo pesimista para evitar colisiones con registros concurrentes de mantenimientos
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
                            'fecha_adquisicion' => 'La fecha de adquisición no puede ser posterior al mantenimiento más antiguo del equipo ('.$mantenimientoMasAntiguo->fecha->format('d/m/Y').').',
                        ]);
                    }
                }

                if ($request->hasFile('imagen')) {
                    $archivoNuevo = $request->file('imagen')->store('equipos', 'public');
                    $datos['imagen'] = $archivoNuevo;
                    $archivoAnteriorParaBorrar = $equipoBloqueado->imagen;
                } else {
                    // Conservar la imagen actual si no se adjuntó una nueva
                    unset($datos['imagen']);
                }

                $equipoBloqueado->update($datos);
            });

            // Borrar el archivo anterior únicamente después de confirmar el éxito en la base de datos
            if ($archivoAnteriorParaBorrar && Storage::disk('public')->exists($archivoAnteriorParaBorrar)) {
                Storage::disk('public')->delete($archivoAnteriorParaBorrar);
            }
        } catch (\Throwable $e) {
            // Si falla el cambio en base de datos, compensar borrando el archivo nuevo y conservar el anterior
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
     * [CRUD-DELETE] [REGLA-NEGOCIO] [SEGURIDAD]
     * Elimina el equipo únicamente si no tiene historial de mantenimientos.
     * Protege contra concurrencia con lockForUpdate y captura de QueryException.
     * Solo borra la imagen física si la eliminación del registro en base de datos fue confirmada.
     */
    public function destroy(Equipo $equipo): RedirectResponse
    {
        try {
            $archivoABorrar = null;

            DB::transaction(function () use ($equipo, &$archivoABorrar): void {
                $equipoBloqueado = Equipo::query()->lockForUpdate()->findOrFail($equipo->id);

                if ($equipoBloqueado->mantenimientos()->exists()) {
                    throw new LogicException('tiene_historial');
                }

                $archivoABorrar = $equipoBloqueado->imagen;
                $equipoBloqueado->delete();
            });

            // Borrar el archivo físico solo después de que el registro fue efectivamente eliminado
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
