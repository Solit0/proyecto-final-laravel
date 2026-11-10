<?php

namespace Tests\Feature;

use App\Models\Equipo;
use App\Models\Laboratorio;
use App\Models\Mantenimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

class ReporteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            throw new LogicException('Estas pruebas requieren SQLite en memoria; no deben modificar Supabase.');
        }

        $this->artisan('migrate', ['--force' => true])->run();

        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        parent::tearDown();
    }

    /**
     * [CRUD-READ] [REGLA-NEGOCIO]
     * El panel de inicio debe calcular los indicadores en tiempo real:
     * total de equipos, conteos por estado, laboratorios y mantenimientos.
     */
    public function test_inicio_muestra_indicadores_reales_del_inventario(): void
    {
        $lab1 = Laboratorio::factory()->create(['nombre' => 'Lab de Redes']);
        $lab2 = Laboratorio::factory()->create(['nombre' => 'Lab de Software']);

        // 3 operativos, 2 en reparación, 1 de baja
        Equipo::factory()->count(3)->create([
            'laboratorio_id' => $lab1->id,
            'estado' => Equipo::ESTADO_OPERATIVO,
        ]);

        Equipo::factory()->count(2)->create([
            'laboratorio_id' => $lab2->id,
            'estado' => Equipo::ESTADO_EN_REPARACION,
        ]);

        $equipoBaja = Equipo::factory()->create([
            'laboratorio_id' => $lab1->id,
            'estado' => Equipo::ESTADO_DE_BAJA,
        ]);

        Mantenimiento::factory()->create([
            'equipo_id' => $equipoBaja->id,
            'fecha' => '2026-01-15',
        ]);

        $respuesta = $this->get(route('panel.index'));

        $respuesta->assertOk();
        $respuesta->assertViewIs('panel.index');
        $respuesta->assertViewHas('totalEquipos', 6);
        $respuesta->assertViewHas('totalOperativos', 3);
        $respuesta->assertViewHas('totalEnReparacion', 2);
        $respuesta->assertViewHas('totalDeBaja', 1);
        $respuesta->assertViewHas('totalLaboratorios', 2);
        $respuesta->assertViewHas('totalMantenimientos', 1);

        $respuesta->assertSee('Estado General del Inventario');
        $respuesta->assertSee(route('reportes.index'));
    }

    /**
     * [CRUD-READ] [RELACION]
     * El reporte 1 debe agrupar equipos por laboratorio y estado,
     * e incluir laboratorios sin equipos con conteo en cero.
     */
    public function test_reporte_muestra_distribucion_por_laboratorio_y_estado_incluyendo_laboratorio_vacio(): void
    {
        $labConEquipos = Laboratorio::factory()->create([
            'nombre' => 'Laboratorio A',
            'capacidad' => 25,
        ]);
        $labVacio = Laboratorio::factory()->create([
            'nombre' => 'Laboratorio B Vacio',
            'capacidad' => 15,
        ]);

        Equipo::factory()->create([
            'laboratorio_id' => $labConEquipos->id,
            'estado' => Equipo::ESTADO_OPERATIVO,
        ]);
        Equipo::factory()->create([
            'laboratorio_id' => $labConEquipos->id,
            'estado' => Equipo::ESTADO_EN_REPARACION,
        ]);

        $respuesta = $this->get(route('reportes.index'));

        $respuesta->assertOk();
        $respuesta->assertViewIs('reportes.index');

        $distribucion = $respuesta->viewData('distribucionLaboratorios');
        $this->assertCount(2, $distribucion);

        $labVacioResult = $distribucion->firstWhere('id', $labVacio->id);
        $this->assertNotNull($labVacioResult);
        $this->assertSame(0, $labVacioResult->equipos_count);
        $this->assertSame(0, $labVacioResult->equipos_operativos_count);
        $this->assertSame(0, $labVacioResult->equipos_en_reparacion_count);
        $this->assertSame(0, $labVacioResult->equipos_de_baja_count);

        $labConResult = $distribucion->firstWhere('id', $labConEquipos->id);
        $this->assertSame(2, $labConResult->equipos_count);
        $this->assertSame(1, $labConResult->equipos_operativos_count);
        $this->assertSame(1, $labConResult->equipos_en_reparacion_count);

        $respuesta->assertSee('Laboratorio B Vacio');
    }

    /**
     * [CRUD-READ] [REGLA-NEGOCIO]
     * El reporte 2 calcula el costo acumulado de mantenimiento por equipo mediante withSum,
     * mostrando cero para equipos que nunca han recibido mantenimiento.
     */
    public function test_reporte_calcula_costo_total_acumulado_por_equipo_y_muestra_cero_sin_historial(): void
    {
        $lab = Laboratorio::factory()->create();

        $equipoConMantenimientos = Equipo::factory()->create([
            'laboratorio_id' => $lab->id,
            'codigo_inventario' => 'LAB-0100',
            'fecha_adquisicion' => '2024-01-01',
        ]);

        Mantenimiento::factory()->create([
            'equipo_id' => $equipoConMantenimientos->id,
            'fecha' => '2024-06-01',
            'costo' => '120.50',
        ]);

        Mantenimiento::factory()->create([
            'equipo_id' => $equipoConMantenimientos->id,
            'fecha' => '2025-02-15',
            'costo' => '79.25',
        ]);

        $equipoSinHistorial = Equipo::factory()->create([
            'laboratorio_id' => $lab->id,
            'codigo_inventario' => 'LAB-0200',
            'fecha_adquisicion' => '2025-01-10',
        ]);

        $respuesta = $this->get(route('reportes.index'));

        $respuesta->assertOk();
        $equiposConCostos = $respuesta->viewData('equiposConCostos');

        $eq1 = $equiposConCostos->firstWhere('id', $equipoConMantenimientos->id);
        $this->assertEquals(199.75, (float) $eq1->mantenimientos_sum_costo);
        $this->assertSame(2, $eq1->mantenimientos_count);

        $eq2 = $equiposConCostos->firstWhere('id', $equipoSinHistorial->id);
        $this->assertNull($eq2->mantenimientos_sum_costo);
        $this->assertSame(0, $eq2->mantenimientos_count);

        $respuesta->assertSee('$199.75');
        $respuesta->assertSee('$0.00');
    }

    /**
     * [CRUD-READ] [REGLA-NEGOCIO]
     * El reporte 3 lista equipos sin mantenimiento en el último año (hoy - 1 año).
     * Regla de corte: un mantenimiento exactamente en la fecha de corte SÍ cuenta dentro del período.
     */
    public function test_reporte_identifica_equipos_sin_mantenimiento_en_el_ultimo_ano_con_regla_de_limite_exacto(): void
    {
        // Fijamos la fecha de prueba: 10 de noviembre de 2026 a mediodía
        Carbon::setTestNow('2026-11-10 12:00:00');
        // El límite de corte de 1 año atrás es 10 de noviembre de 2025 a las 00:00:00

        $lab = Laboratorio::factory()->create();

        // 1. Equipo que NUNCA tuvo mantenimientos -> DEBE APARECER
        $equipoNuncaMantenido = Equipo::factory()->create([
            'laboratorio_id' => $lab->id,
            'codigo_inventario' => 'LAB-SIN-01',
            'fecha_adquisicion' => '2024-01-01',
        ]);

        // 2. Equipo con mantenimiento antiguo (9 de noviembre de 2025: más de 1 año) -> DEBE APARECER
        $equipoMantenimientoAntiguo = Equipo::factory()->create([
            'laboratorio_id' => $lab->id,
            'codigo_inventario' => 'LAB-ANT-02',
            'fecha_adquisicion' => '2024-01-01',
        ]);
        Mantenimiento::factory()->create([
            'equipo_id' => $equipoMantenimientoAntiguo->id,
            'fecha' => '2025-11-09',
        ]);

        // 3. Equipo con mantenimiento EXACTO en el límite (10 de noviembre de 2025) -> NO DEBE APARECER
        $equipoEnElLimite = Equipo::factory()->create([
            'laboratorio_id' => $lab->id,
            'codigo_inventario' => 'LAB-LIM-03',
            'fecha_adquisicion' => '2024-01-01',
        ]);
        Mantenimiento::factory()->create([
            'equipo_id' => $equipoEnElLimite->id,
            'fecha' => '2025-11-10',
        ]);

        // 4. Equipo con mantenimiento reciente (2026-05-01) -> NO DEBE APARECER
        $equipoReciente = Equipo::factory()->create([
            'laboratorio_id' => $lab->id,
            'codigo_inventario' => 'LAB-REC-04',
            'fecha_adquisicion' => '2024-01-01',
        ]);
        Mantenimiento::factory()->create([
            'equipo_id' => $equipoReciente->id,
            'fecha' => '2026-05-01',
        ]);

        $respuesta = $this->get(route('reportes.index'));

        $respuesta->assertOk();
        $sinMantenimiento = $respuesta->viewData('equiposSinMantenimientoAnual');

        $codigos = $sinMantenimiento->pluck('codigo_inventario')->all();

        $this->assertContains('LAB-SIN-01', $codigos);
        $this->assertContains('LAB-ANT-02', $codigos);
        $this->assertNotContains('LAB-LIM-03', $codigos);
        $this->assertNotContains('LAB-REC-04', $codigos);

        $respuesta->assertSee('LAB-SIN-01');
        $respuesta->assertSee('LAB-ANT-02');
        $respuesta->assertSee('Sin mantenimientos registrados');
    }
}
