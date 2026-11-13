<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMantenimientoRequest;
use App\Models\Equipo;
use App\Models\Mantenimiento;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MantenimientoController extends Controller
{
    /**
     * Listado paginado de mantenimientos con equipo y laboratorio.
     */
    public function index(): View
    {
        $mantenimientos = Mantenimiento::query()
            ->with('equipo.laboratorio')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(10);

        return view('mantenimientos.index', ['mantenimientos' => $mantenimientos]);
    }

    /**
     * Formulario de registro (excluye equipos dados de baja).
     */
    public function create(): View
    {
        $equipos = Equipo::query()
            ->with('laboratorio')
            ->where('estado', '!=', Equipo::ESTADO_DE_BAJA)
            ->orderBy('codigo_inventario')
            ->get();

        return view('mantenimientos.create', ['equipos' => $equipos]);
    }

    /**
     * Registra un nuevo mantenimiento.
     */
    public function store(StoreMantenimientoRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        DB::transaction(function () use ($datos): void {
            $equipo = Equipo::query()->lockForUpdate()->find($datos['equipo_id']);

            if ($equipo === null) {
                throw ValidationException::withMessages([
                    'equipo_id' => 'El equipo seleccionado ya no existe.',
                ]);
            }

            $this->comprobarEquipoYFecha($equipo, $datos['fecha']);

            Mantenimiento::query()->create($datos);

            if ($datos['tipo'] === Mantenimiento::TIPO_CORRECTIVO) {
                // El mantenimiento correctivo cambia el estado del equipo a en reparación
                $equipo->update(['estado' => Equipo::ESTADO_EN_REPARACION]);
            }
        });

        return redirect()
            ->route('mantenimientos.index')
            ->with('exito', 'Mantenimiento registrado correctamente.');
    }

    /**
     * Valida estado y fecha contra la adquisición con la fila bloqueada.
     */
    private function comprobarEquipoYFecha(Equipo $equipo, string $fecha): void
    {
        if ($equipo->estado === Equipo::ESTADO_DE_BAJA) {
            throw ValidationException::withMessages([
                'equipo_id' => 'No se puede registrar un mantenimiento para un equipo dado de baja.',
            ]);
        }

        if (Carbon::parse($fecha)->startOfDay()->lt($equipo->fecha_adquisicion)) {
            throw ValidationException::withMessages([
                'fecha' => 'La fecha del mantenimiento no puede ser anterior a la fecha de adquisición del equipo.',
            ]);
        }
    }
}
