<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Laboratorio;
use App\Models\Mantenimiento;
use Carbon\Carbon;
use Illuminate\View\View;

class PanelController extends Controller
{
    /**
     * Panel principal con indicadores generales del inventario.
     */
    public function index(): View
    {
        try {
            $totalEquipos = Equipo::query()->count();

            $conteosPorEstado = Equipo::query()
                ->selectRaw('estado, COUNT(*) as total')
                ->groupBy('estado')
                ->pluck('total', 'estado');

            $totalOperativos = (int) $conteosPorEstado->get(Equipo::ESTADO_OPERATIVO, 0);
            $totalEnReparacion = (int) $conteosPorEstado->get(Equipo::ESTADO_EN_REPARACION, 0);
            $totalDeBaja = (int) $conteosPorEstado->get(Equipo::ESTADO_DE_BAJA, 0);

            $totalLaboratorios = Laboratorio::query()->count();
            $totalMantenimientos = Mantenimiento::query()->count();
        } catch (\Throwable) {
            $totalEquipos = 0;
            $totalOperativos = 0;
            $totalEnReparacion = 0;
            $totalDeBaja = 0;
            $totalLaboratorios = 0;
            $totalMantenimientos = 0;
        }

        return view('panel.index', [
            'totalEquipos' => $totalEquipos,
            'totalOperativos' => $totalOperativos,
            'totalEnReparacion' => $totalEnReparacion,
            'totalDeBaja' => $totalDeBaja,
            'totalLaboratorios' => $totalLaboratorios,
            'totalMantenimientos' => $totalMantenimientos,
        ]);
    }

    /**
     * Reportes de distribución, costos y equipos sin atención reciente.
     */
    public function reporte(): View
    {
        // 1. Distribución de equipos por laboratorio y estado
        $distribucionLaboratorios = Laboratorio::query()
            ->withCount([
                'equipos',
                'equipos as equipos_operativos_count' => fn ($query) => $query->where('estado', Equipo::ESTADO_OPERATIVO),
                'equipos as equipos_en_reparacion_count' => fn ($query) => $query->where('estado', Equipo::ESTADO_EN_REPARACION),
                'equipos as equipos_de_baja_count' => fn ($query) => $query->where('estado', Equipo::ESTADO_DE_BAJA),
            ])
            ->orderBy('nombre')
            ->get();

        // 2. Costo total de mantenimientos acumulado por equipo
        $equiposConCostos = Equipo::query()
            ->with('laboratorio')
            ->withSum('mantenimientos', 'costo')
            ->withCount('mantenimientos')
            ->orderBy('codigo_inventario')
            ->get();

        // 3. Equipos sin mantenimientos en el último año (hoy menos 1 año)
        $limiteUnAno = Carbon::now()->subYear()->startOfDay();

        $equiposSinMantenimientoAnual = Equipo::query()
            ->with([
                'laboratorio',
                'mantenimientos' => fn ($query) => $query->orderByDesc('fecha')->orderByDesc('id'),
            ])
            ->whereDoesntHave('mantenimientos', function ($query) use ($limiteUnAno): void {
                $query->where('fecha', '>=', $limiteUnAno);
            })
            ->orderBy('codigo_inventario')
            ->get();

        return view('reportes.index', [
            'distribucionLaboratorios' => $distribucionLaboratorios,
            'equiposConCostos' => $equiposConCostos,
            'equiposSinMantenimientoAnual' => $equiposSinMantenimientoAnual,
            'limiteUnAno' => $limiteUnAno,
        ]);
    }
}
