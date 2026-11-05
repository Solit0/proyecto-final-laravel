<?php

namespace Tests\Feature;

use App\Models\Equipo;
use App\Models\Laboratorio;
use App\Models\Mantenimiento;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use LogicException;
use Tests\TestCase;

class EquipoTest extends TestCase
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
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        parent::tearDown();
    }

    public function test_listado_muestra_equipos_con_laboratorio_y_paginacion_de_diez(): void
    {
        $laboratorio = Laboratorio::factory()->create(['nombre' => 'Lab Redes']);

        for ($i = 1; $i <= 15; $i++) {
            Equipo::factory()->create([
                'laboratorio_id' => $laboratorio->id,
                'codigo_inventario' => sprintf('LAB-%04d', $i),
            ]);
        }

        $respuesta = $this->get(route('equipos.index'));

        $respuesta->assertOk();
        $equipos = $respuesta->viewData('equipos');
        $this->assertCount(10, $equipos);
        $this->assertSame(15, $equipos->total());
        $this->assertSame('LAB-0001', $equipos->first()->codigo_inventario);
        $this->assertSame('LAB-0010', $equipos->last()->codigo_inventario);
    }

    public function test_busqueda_por_q_es_insensible_a_mayusculas_y_agrupa_condiciones_or(): void
    {
        $labA = Laboratorio::factory()->create(['nombre' => 'Lab A']);
        $labB = Laboratorio::factory()->create(['nombre' => 'Lab B']);

        Equipo::factory()->create([
            'laboratorio_id' => $labA->id,
            'codigo_inventario' => 'LAB-0010',
            'marca' => 'Dell',
            'modelo' => 'OptiPlex 7090',
            'estado' => Equipo::ESTADO_OPERATIVO,
        ]);
        Equipo::factory()->create([
            'laboratorio_id' => $labA->id,
            'codigo_inventario' => 'LAB-0020',
            'marca' => 'HP',
            'modelo' => 'ProDesk',
            'estado' => Equipo::ESTADO_EN_REPARACION,
        ]);
        Equipo::factory()->create([
            'laboratorio_id' => $labB->id,
            'codigo_inventario' => 'LAB-0030',
            'marca' => 'Dell',
            'modelo' => 'Latitude',
            'estado' => Equipo::ESTADO_OPERATIVO,
        ]);

        // Búsqueda por marca en minúsculas y filtro por laboratorio
        $respuesta = $this->get(route('equipos.index', [
            'q' => 'dell',
            'laboratorio_id' => $labA->id,
        ]));

        $respuesta->assertOk();
        $equipos = $respuesta->viewData('equipos');
        $this->assertCount(1, $equipos);
        $this->assertSame('LAB-0010', $equipos->first()->codigo_inventario);
    }

    public function test_filtros_combinados_de_laboratorio_y_estado_se_conservan_en_paginacion(): void
    {
        $lab = Laboratorio::factory()->create();

        for ($i = 1; $i <= 12; $i++) {
            Equipo::factory()->create([
                'laboratorio_id' => $lab->id,
                'codigo_inventario' => sprintf('LAB-%04d', $i),
                'estado' => Equipo::ESTADO_OPERATIVO,
            ]);
        }

        $respuesta = $this->get(route('equipos.index', [
            'laboratorio_id' => $lab->id,
            'estado' => Equipo::ESTADO_OPERATIVO,
        ]));

        $respuesta->assertOk();
        $equipos = $respuesta->viewData('equipos');
        $this->assertSame(12, $equipos->total());
        $this->assertStringContainsString('laboratorio_id='.$lab->id, $equipos->nextPageUrl());
        $this->assertStringContainsString('estado=operativo', $equipos->nextPageUrl());
    }

    public function test_ficha_de_equipo_carga_laboratorio_e_historial_ordenado_descendentemente(): void
    {
        $laboratorio = Laboratorio::factory()->create(['nombre' => 'Lab Multimedia']);
        $equipo = Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-0050',
            'fecha_adquisicion' => '2024-01-10',
        ]);

        $m1 = Mantenimiento::factory()->create([
            'equipo_id' => $equipo->id,
            'fecha' => '2024-02-15',
            'tipo' => Mantenimiento::TIPO_PREVENTIVO,
        ]);
        $m2 = Mantenimiento::factory()->create([
            'equipo_id' => $equipo->id,
            'fecha' => '2024-06-20',
            'tipo' => Mantenimiento::TIPO_CORRECTIVO,
        ]);

        $respuesta = $this->get(route('equipos.show', $equipo));

        $respuesta->assertOk();
        $equipoCargado = $respuesta->viewData('equipo');
        $this->assertTrue($equipoCargado->relationLoaded('laboratorio'));
        $this->assertTrue($equipoCargado->relationLoaded('mantenimientos'));
        $this->assertSame($m2->id, $equipoCargado->mantenimientos->first()->id);
        $this->assertSame($m1->id, $equipoCargado->mantenimientos->last()->id);
    }

    public function test_equipo_inexistente_retorna_404(): void
    {
        $respuesta = $this->get('/equipos/999999');

        $respuesta->assertNotFound();
    }

    public function test_creacion_exitosa_con_codigo_valido_costo_cero_y_adquisicion_hoy(): void
    {
        $laboratorio = Laboratorio::factory()->create();

        $datos = [
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-0001',
            'tipo' => 'computadora',
            'marca' => 'Lenovo',
            'modelo' => 'ThinkCentre M70q',
            'numero_serie' => 'SER-LENOVO-0001',
            'fecha_adquisicion' => today()->toDateString(),
            'costo' => '0.00',
            'estado' => 'operativo',
        ];

        $respuesta = $this->post(route('equipos.store'), $datos);

        $equipo = Equipo::query()->where('codigo_inventario', 'LAB-0001')->firstOrFail();
        $respuesta->assertRedirect(route('equipos.show', $equipo));
        $respuesta->assertSessionHas('exito');

        $this->assertDatabaseHas('equipos', [
            'codigo_inventario' => 'LAB-0001',
            'costo' => 0.00,
            'estado' => 'operativo',
        ]);
    }

    public function test_creacion_rechaza_codigo_con_formato_invalido(): void
    {
        $laboratorio = Laboratorio::factory()->create();

        $casosInvalidos = ['LAB-01', 'LAB-00001', 'lab-0001', 'LAB-000A', 'INV-0001'];

        foreach ($casosInvalidos as $codigoInvalido) {
            $respuesta = $this->from(route('equipos.create'))->post(route('equipos.store'), [
                'laboratorio_id' => $laboratorio->id,
                'codigo_inventario' => $codigoInvalido,
                'tipo' => 'computadora',
                'marca' => 'Dell',
                'modelo' => 'OptiPlex',
                'numero_serie' => 'SER-'.fake()->unique()->numerify('####'),
                'fecha_adquisicion' => today()->toDateString(),
                'costo' => '500.00',
                'estado' => 'operativo',
            ]);

            $respuesta->assertRedirect(route('equipos.create'));
            $respuesta->assertSessionHasErrors(['codigo_inventario']);
        }
    }

    public function test_creacion_rechaza_codigo_o_serie_duplicados(): void
    {
        $laboratorio = Laboratorio::factory()->create();

        Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-0001',
            'numero_serie' => 'SER-EXISTENTE-01',
        ]);

        $respuesta = $this->from(route('equipos.create'))->post(route('equipos.store'), [
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-0001',
            'tipo' => 'computadora',
            'marca' => 'HP',
            'modelo' => 'ProDesk',
            'numero_serie' => 'SER-EXISTENTE-01',
            'fecha_adquisicion' => today()->toDateString(),
            'costo' => '300.00',
            'estado' => 'operativo',
        ]);

        $respuesta->assertRedirect(route('equipos.create'));
        $respuesta->assertSessionHasErrors(['codigo_inventario', 'numero_serie']);
    }

    public function test_creacion_rechaza_adquisicion_futura_y_costo_negativo(): void
    {
        $laboratorio = Laboratorio::factory()->create();

        $respuesta = $this->from(route('equipos.create'))->post(route('equipos.store'), [
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-0002',
            'tipo' => 'impresora',
            'marca' => 'Epson',
            'modelo' => 'L3250',
            'numero_serie' => 'SER-EPSON-0002',
            'fecha_adquisicion' => today()->addDay()->toDateString(),
            'costo' => '-50.00',
            'estado' => 'operativo',
        ]);

        $respuesta->assertRedirect(route('equipos.create'));
        $respuesta->assertSessionHasErrors(['fecha_adquisicion', 'costo']);
    }

    public function test_creacion_con_imagen_valida_almacena_archivo_y_ruta(): void
    {
        Storage::fake('public');
        $laboratorio = Laboratorio::factory()->create();

        $imagen = UploadedFile::fake()->image('equipo.png', 640, 480);

        $respuesta = $this->post(route('equipos.store'), [
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-0003',
            'tipo' => 'switch',
            'marca' => 'Cisco',
            'modelo' => 'Catalyst 2960',
            'numero_serie' => 'SER-CISCO-0003',
            'fecha_adquisicion' => today()->toDateString(),
            'costo' => '1200.00',
            'estado' => 'operativo',
            'imagen' => $imagen,
        ]);

        $equipo = Equipo::query()->where('codigo_inventario', 'LAB-0003')->firstOrFail();
        $this->assertNotNull($equipo->imagen);
        Storage::disk('public')->assertExists($equipo->imagen);
        $respuesta->assertRedirect(route('equipos.show', $equipo));
    }

    public function test_edicion_permite_guardar_sin_cambiar_codigo_ni_serie(): void
    {
        $laboratorio = Laboratorio::factory()->create();
        $equipo = Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-0004',
            'numero_serie' => 'SER-PROPIO-0004',
            'marca' => 'HP',
        ]);

        $respuesta = $this->put(route('equipos.update', $equipo), [
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-0004',
            'tipo' => $equipo->tipo,
            'marca' => 'HP Actualizada',
            'modelo' => $equipo->modelo,
            'numero_serie' => 'SER-PROPIO-0004',
            'fecha_adquisicion' => $equipo->fecha_adquisicion->toDateString(),
            'costo' => (string) $equipo->costo,
            'estado' => $equipo->estado,
        ]);

        $respuesta->assertRedirect(route('equipos.show', $equipo));
        $respuesta->assertSessionHas('exito');
        $this->assertSame('HP Actualizada', $equipo->fresh()->marca);
    }

    public function test_edicion_rechaza_codigo_o_serie_ocupados_por_otro_equipo(): void
    {
        $laboratorio = Laboratorio::factory()->create();
        $otroEquipo = Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-0005',
            'numero_serie' => 'SER-OTRO-0005',
        ]);
        $equipo = Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-0006',
            'numero_serie' => 'SER-ACTUAL-0006',
        ]);

        $respuesta = $this->from(route('equipos.edit', $equipo))->put(route('equipos.update', $equipo), [
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-0005',
            'tipo' => $equipo->tipo,
            'marca' => $equipo->marca,
            'modelo' => $equipo->modelo,
            'numero_serie' => 'SER-OTRO-0005',
            'fecha_adquisicion' => $equipo->fecha_adquisicion->toDateString(),
            'costo' => (string) $equipo->costo,
            'estado' => $equipo->estado,
        ]);

        $respuesta->assertRedirect(route('equipos.edit', $equipo));
        $respuesta->assertSessionHasErrors(['codigo_inventario', 'numero_serie']);
    }

    public function test_edicion_rechaza_adquisicion_posterior_al_primer_mantenimiento(): void
    {
        $laboratorio = Laboratorio::factory()->create();
        $equipo = Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'fecha_adquisicion' => '2024-01-10',
        ]);

        Mantenimiento::factory()->create([
            'equipo_id' => $equipo->id,
            'fecha' => '2024-02-01',
        ]);

        $respuesta = $this->from(route('equipos.edit', $equipo))->put(route('equipos.update', $equipo), [
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => $equipo->codigo_inventario,
            'tipo' => $equipo->tipo,
            'marca' => $equipo->marca,
            'modelo' => $equipo->modelo,
            'numero_serie' => $equipo->numero_serie,
            'fecha_adquisicion' => '2024-02-02', // Posterior al mantenimiento
            'costo' => (string) $equipo->costo,
            'estado' => $equipo->estado,
        ]);

        $respuesta->assertRedirect(route('equipos.edit', $equipo));
        $respuesta->assertSessionHasErrors(['fecha_adquisicion']);
        $this->assertSame('2024-01-10', $equipo->fresh()->fecha_adquisicion->toDateString());
    }

    public function test_edicion_acepta_adquisicion_anterior_o_igual_al_primer_mantenimiento(): void
    {
        $laboratorio = Laboratorio::factory()->create();
        $equipo = Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'fecha_adquisicion' => '2024-01-10',
        ]);

        Mantenimiento::factory()->create([
            'equipo_id' => $equipo->id,
            'fecha' => '2024-02-01',
        ]);

        $respuesta = $this->put(route('equipos.update', $equipo), [
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => $equipo->codigo_inventario,
            'tipo' => $equipo->tipo,
            'marca' => $equipo->marca,
            'modelo' => $equipo->modelo,
            'numero_serie' => $equipo->numero_serie,
            'fecha_adquisicion' => '2024-02-01', // Mismo día del mantenimiento
            'costo' => (string) $equipo->costo,
            'estado' => $equipo->estado,
        ]);

        $respuesta->assertRedirect(route('equipos.show', $equipo));
        $this->assertSame('2024-02-01', $equipo->fresh()->fecha_adquisicion->toDateString());
    }

    public function test_eliminar_equipo_sin_mantenimientos_elimina_registro_e_imagen(): void
    {
        Storage::fake('public');
        $laboratorio = Laboratorio::factory()->create();

        $ruta = Storage::disk('public')->putFile('equipos', UploadedFile::fake()->image('foto.jpg'));

        $equipo = Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'imagen' => $ruta,
        ]);

        Storage::disk('public')->assertExists($ruta);

        $respuesta = $this->delete(route('equipos.destroy', $equipo));

        $respuesta->assertRedirect(route('equipos.index'));
        $respuesta->assertSessionHas('exito');
        $this->assertDatabaseMissing('equipos', ['id' => $equipo->id]);
        Storage::disk('public')->assertMissing($ruta);
    }

    public function test_eliminar_equipo_con_mantenimientos_es_rechazado_y_protege_registro_e_imagen(): void
    {
        Storage::fake('public');
        $laboratorio = Laboratorio::factory()->create();

        $ruta = Storage::disk('public')->putFile('equipos', UploadedFile::fake()->image('foto2.jpg'));

        $equipo = Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'imagen' => $ruta,
        ]);

        Mantenimiento::factory()->create([
            'equipo_id' => $equipo->id,
            'fecha' => '2024-03-01',
        ]);

        $respuesta = $this->delete(route('equipos.destroy', $equipo));

        $respuesta->assertRedirect(route('equipos.index'));
        $respuesta->assertSessionHas('error', 'No se puede eliminar porque tiene historial; puede marcarse de baja.');
        $this->assertDatabaseHas('equipos', ['id' => $equipo->id]);
        Storage::disk('public')->assertExists($ruta);
    }

    public function test_no_existe_ruta_get_que_elimine_equipos(): void
    {
        $laboratorio = Laboratorio::factory()->create();
        $equipo = Equipo::factory()->create(['laboratorio_id' => $laboratorio->id]);

        $respuesta = $this->get('/equipos/'.$equipo->id.'/delete');
        $this->assertTrue(in_array($respuesta->status(), [404, 405], true));

        $this->assertDatabaseHas('equipos', ['id' => $equipo->id]);
    }
}
