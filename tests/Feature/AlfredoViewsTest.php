<?php

namespace Tests\Feature;

use App\Models\Equipo;
use App\Models\Laboratorio;
use App\Models\Mantenimiento;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

class AlfredoViewsTest extends TestCase
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

    public function test_inicio_carga_correctamente_con_layout_y_navegacion(): void
    {
        $respuesta = $this->get('/');

        $respuesta->assertOk();
        $respuesta->assertSee('Inventario de Laboratorios');
        $respuesta->assertSee('css/estilos.css');
        $respuesta->assertSee(route('equipos.index'));
        $respuesta->assertSee(route('mantenimientos.index'));
    }

    public function test_listado_de_equipos_muestra_estado_vacio_sin_equipos(): void
    {
        $respuesta = $this->get(route('equipos.index'));

        $respuesta->assertOk();
        $respuesta->assertSee('No hay equipos registrados');
        $respuesta->assertSee(route('equipos.create'));
    }

    public function test_listado_de_equipos_muestra_estado_vacio_con_filtros_sin_resultados(): void
    {
        $lab = Laboratorio::factory()->create();
        Equipo::factory()->create(['laboratorio_id' => $lab->id, 'codigo_inventario' => 'LAB-0001']);

        $respuesta = $this->get(route('equipos.index', ['q' => 'inexistente']));

        $respuesta->assertOk();
        $respuesta->assertSee('Sin resultados para estos filtros');
        $respuesta->assertSee('Restablecer filtros');
    }

    public function test_listado_de_equipos_muestra_imagen_predeterminada_cuando_no_hay_foto(): void
    {
        $lab = Laboratorio::factory()->create();
        $equipo = Equipo::factory()->create([
            'laboratorio_id' => $lab->id,
            'codigo_inventario' => 'LAB-0010',
            'imagen' => null,
        ]);

        $respuesta = $this->get(route('equipos.index'));

        $respuesta->assertOk();
        $respuesta->assertSee('images/equipo-default.svg');
        $respuesta->assertSee($equipo->codigo_inventario);
    }

    public function test_ficha_de_equipo_muestra_datos_laboratorio_y_aviso_sin_mantenimientos(): void
    {
        $lab = Laboratorio::factory()->create([
            'nombre' => 'Lab de Robótica',
            'ubicacion' => 'Edificio C, Aula 3',
        ]);
        $equipo = Equipo::factory()->create([
            'laboratorio_id' => $lab->id,
            'codigo_inventario' => 'LAB-0020',
        ]);

        $respuesta = $this->get(route('equipos.show', $equipo));

        $respuesta->assertOk();
        $respuesta->assertSee('LAB-0020');
        $respuesta->assertSee('Lab de Robótica');
        $respuesta->assertSee('Edificio C, Aula 3');
        $respuesta->assertSee('Este equipo aún no tiene mantenimientos.');
    }

    public function test_formulario_crear_equipo_renderiza_parcial_y_atributos(): void
    {
        $lab = Laboratorio::factory()->create(['nombre' => 'Lab Sistemas']);

        $respuesta = $this->get(route('equipos.create'));

        $respuesta->assertOk();
        $respuesta->assertSee('novalidate', false);
        $respuesta->assertSee('multipart/form-data', false);
        $respuesta->assertSee('LAB-0000');
        $respuesta->assertSee('Lab Sistemas');
        $respuesta->assertSee('Guardar equipo');
    }

    public function test_formulario_editar_equipo_renderiza_valores_existentes_y_metodo_put(): void
    {
        $lab = Laboratorio::factory()->create();
        $equipo = Equipo::factory()->create([
            'laboratorio_id' => $lab->id,
            'codigo_inventario' => 'LAB-0030',
            'marca' => 'Dell Inc',
        ]);

        $respuesta = $this->get(route('equipos.edit', $equipo));

        $respuesta->assertOk();
        $respuesta->assertSee('value="PUT"', false);
        $respuesta->assertSee('LAB-0030');
        $respuesta->assertSee('Dell Inc');
        $respuesta->assertSee('Actualizar equipo');
    }

    public function test_listado_mantenimientos_muestra_vacio_y_con_registros(): void
    {
        $respuestaVacia = $this->get(route('mantenimientos.index'));
        $respuestaVacia->assertOk();
        $respuestaVacia->assertSee('Todavía no hay mantenimientos registrados');

        $lab = Laboratorio::factory()->create();
        $equipo = Equipo::factory()->create(['laboratorio_id' => $lab->id, 'codigo_inventario' => 'LAB-0040']);
        $mantenimiento = Mantenimiento::factory()->create([
            'equipo_id' => $equipo->id,
            'descripcion' => 'Mantenimiento semestral preventivo',
            'tecnico_responsable' => 'Carlos Méndez',
        ]);

        $respuestaConDatos = $this->get(route('mantenimientos.index'));
        $respuestaConDatos->assertOk();
        $respuestaConDatos->assertSee('LAB-0040');
        $respuestaConDatos->assertSee('Mantenimiento semestral preventivo');
        $respuestaConDatos->assertSee('Carlos Méndez');
    }

    public function test_formulario_crear_mantenimiento_muestra_nota_correctivo(): void
    {
        $lab = Laboratorio::factory()->create();
        Equipo::factory()->create([
            'laboratorio_id' => $lab->id,
            'codigo_inventario' => 'LAB-0050',
            'estado' => Equipo::ESTADO_OPERATIVO,
        ]);

        $respuesta = $this->get(route('mantenimientos.create'));

        $respuesta->assertOk();
        $respuesta->assertSee('novalidate', false);
        $respuesta->assertSee('El mantenimiento correctivo dejará automáticamente el equipo en estado "En reparación"', false);
        $respuesta->assertSee('LAB-0050');
    }
}
