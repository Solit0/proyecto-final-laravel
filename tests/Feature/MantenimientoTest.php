<?php

namespace Tests\Feature;

use App\Models\Equipo;
use App\Models\Laboratorio;
use App\Models\Mantenimiento;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use LogicException;
use RuntimeException;
use Tests\TestCase;

class MantenimientoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            throw new LogicException('Estas pruebas requieren SQLite en memoria; no deben modificar Supabase.');
        }

        // Se ejecutan las migraciones reales sobre la base efímera, sin reiniciar Supabase.
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

    public function test_las_relaciones_permiten_navegar_entre_laboratorio_equipo_y_mantenimiento(): void
    {
        $laboratorio = Laboratorio::factory()->create();
        $equipo = Equipo::factory()->create(['laboratorio_id' => $laboratorio->id]);
        $mantenimiento = Mantenimiento::factory()->create(['equipo_id' => $equipo->id]);

        $this->assertTrue($laboratorio->equipos->contains($equipo));
        $this->assertTrue($equipo->mantenimientos->contains($mantenimiento));
        $this->assertTrue($mantenimiento->equipo->is($equipo));
        $this->assertTrue($equipo->laboratorio->is($laboratorio));
    }

    public function test_fecha_igual_a_la_adquisicion_se_acepta(): void
    {
        $equipo = $this->crearEquipo();

        $respuesta = $this->post(route('mantenimientos.store'), $this->datos($equipo, [
            'fecha' => '2024-01-15',
            'tipo' => Mantenimiento::TIPO_PREVENTIVO,
        ]));

        $respuesta->assertRedirect(route('mantenimientos.index'));
        $respuesta->assertSessionHas('exito');

        $mantenimiento = Mantenimiento::query()->sole();
        $this->assertSame('2024-01-15', $mantenimiento->fecha->toDateString());
        $this->assertSame(Equipo::ESTADO_OPERATIVO, $equipo->fresh()->estado);
    }

    public function test_fecha_anterior_a_la_adquisicion_se_rechaza_sin_insertar(): void
    {
        $equipo = $this->crearEquipo();

        $respuesta = $this->from(route('mantenimientos.create'))->post(route('mantenimientos.store'), $this->datos($equipo, [
            'fecha' => '2024-01-14',
        ]));

        $respuesta->assertRedirect(route('mantenimientos.create'));
        $respuesta->assertSessionHasErrors(['fecha']);
        $this->assertDatabaseCount('mantenimientos', 0);
        $this->assertSame(Equipo::ESTADO_OPERATIVO, $equipo->fresh()->estado);
    }

    public function test_equipo_de_baja_se_rechaza_aunque_el_post_sea_manipulado(): void
    {
        $equipo = $this->crearEquipo(['estado' => Equipo::ESTADO_DE_BAJA]);

        $respuesta = $this->from(route('mantenimientos.create'))->post(route('mantenimientos.store'), $this->datos($equipo));

        $respuesta->assertRedirect(route('mantenimientos.create'));
        $respuesta->assertSessionHasErrors([
            'equipo_id' => 'No se puede registrar un mantenimiento para un equipo dado de baja.',
        ]);
        $this->assertDatabaseCount('mantenimientos', 0);
        $this->assertSame(Equipo::ESTADO_DE_BAJA, $equipo->fresh()->estado);
    }

    public function test_equipo_inexistente_genera_error_de_validacion(): void
    {
        $respuesta = $this->from(route('mantenimientos.create'))->post(route('mantenimientos.store'), [
            'equipo_id' => 999999,
            'fecha' => '2024-02-01',
            'tipo' => Mantenimiento::TIPO_PREVENTIVO,
            'descripcion' => 'Revisión general.',
            'tecnico_responsable' => 'Ana Pérez',
            'costo' => '10.00',
        ]);

        $respuesta->assertRedirect(route('mantenimientos.create'));
        $respuesta->assertSessionHasErrors(['equipo_id']);
        $this->assertDatabaseCount('mantenimientos', 0);
    }

    public function test_tipo_fuera_del_catalogo_genera_error_de_validacion(): void
    {
        $equipo = $this->crearEquipo();

        $respuesta = $this->from(route('mantenimientos.create'))->post(route('mantenimientos.store'), $this->datos($equipo, [
            'tipo' => 'limpieza',
        ]));

        $respuesta->assertRedirect(route('mantenimientos.create'));
        $respuesta->assertSessionHasErrors(['tipo']);
        $this->assertDatabaseCount('mantenimientos', 0);
    }

    public function test_preventivo_de_costo_cero_se_acepta_y_no_cambia_el_estado(): void
    {
        $equipo = $this->crearEquipo();

        $respuesta = $this->post(route('mantenimientos.store'), $this->datos($equipo, [
            'tipo' => Mantenimiento::TIPO_PREVENTIVO,
            'costo' => '0.00',
        ]));

        $respuesta->assertRedirect(route('mantenimientos.index'));
        $this->assertDatabaseCount('mantenimientos', 1);
        $this->assertSame('0.00', Mantenimiento::query()->sole()->costo);
        $this->assertSame(Equipo::ESTADO_OPERATIVO, $equipo->fresh()->estado);
    }

    public function test_correctivo_valido_inserta_y_deja_el_equipo_en_reparacion(): void
    {
        $equipo = $this->crearEquipo();

        $respuesta = $this->post(route('mantenimientos.store'), $this->datos($equipo, [
            'tipo' => Mantenimiento::TIPO_CORRECTIVO,
        ]));

        $respuesta->assertRedirect(route('mantenimientos.index'));
        $this->assertDatabaseCount('mantenimientos', 1);
        $this->assertSame(Equipo::ESTADO_EN_REPARACION, $equipo->fresh()->estado);
    }

    public function test_una_falla_de_escritura_no_deja_mantenimiento_ni_cambio_parcial(): void
    {
        $equipo = $this->crearEquipo();
        $fallo = false;

        Equipo::updating(function (): void {
            throw new RuntimeException('Fallo simulado al actualizar el equipo.');
        });

        try {
            $this->withoutExceptionHandling()->post(route('mantenimientos.store'), $this->datos($equipo, [
                'tipo' => Mantenimiento::TIPO_CORRECTIVO,
            ]));
        } catch (RuntimeException $exception) {
            $fallo = true;
            $this->assertSame('Fallo simulado al actualizar el equipo.', $exception->getMessage());
        } finally {
            Equipo::flushEventListeners();
        }

        $this->assertTrue($fallo, 'La escritura debía fallar para comprobar el rollback.');
        $this->assertDatabaseCount('mantenimientos', 0);
        $this->assertSame(Equipo::ESTADO_OPERATIVO, $equipo->fresh()->estado);
    }

    public function test_el_formulario_vacio_con_novalidate_muestra_errores_en_español(): void
    {
        $respuesta = $this->followingRedirects()
            ->from(route('mantenimientos.create'))
            ->post(route('mantenimientos.store'), []);

        $respuesta->assertOk();
        $respuesta->assertSee('novalidate', false);
        $respuesta->assertSee('Seleccione el equipo que recibió el mantenimiento.');
        $respuesta->assertSee('Ingrese la fecha del mantenimiento.');
        $respuesta->assertSee('Seleccione el tipo de mantenimiento.');
        $respuesta->assertSee('Describa el trabajo realizado.');
        $respuesta->assertSee('Ingrese el nombre del técnico responsable.');
        $respuesta->assertSee('Ingrese el costo del mantenimiento.');
        $this->assertDatabaseCount('mantenimientos', 0);
    }

    public function test_el_listado_ordena_por_fecha_descendente_y_pagina_de_diez_en_diez(): void
    {
        $equipo = $this->crearEquipo();

        Mantenimiento::factory()->create([
            'equipo_id' => $equipo->id,
            'fecha' => '2024-01-20',
        ]);
        Mantenimiento::factory()->create([
            'equipo_id' => $equipo->id,
            'fecha' => '2024-06-10',
        ]);

        $respuesta = $this->get(route('mantenimientos.index'));

        $respuesta->assertOk();
        $mantenimientos = $respuesta->viewData('mantenimientos');
        $this->assertSame('2024-06-10', $mantenimientos->first()->fecha->toDateString());
        $this->assertSame(10, $mantenimientos->perPage());
    }

    public function test_no_se_puede_eliminar_un_laboratorio_que_tiene_equipos(): void
    {
        $laboratorio = Laboratorio::factory()->create();
        Equipo::factory()->create(['laboratorio_id' => $laboratorio->id]);

        $this->expectException(QueryException::class);

        $laboratorio->delete();
    }

    public function test_no_se_puede_eliminar_un_equipo_que_tiene_mantenimientos(): void
    {
        $equipo = $this->crearEquipo();
        Mantenimiento::factory()->create(['equipo_id' => $equipo->id]);

        $this->expectException(QueryException::class);

        $equipo->delete();
    }

    public function test_la_base_rechaza_codigos_de_inventario_repetidos(): void
    {
        $laboratorio = Laboratorio::factory()->create();

        Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-0001',
        ]);

        $this->expectException(QueryException::class);

        Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-0001',
        ]);
    }

    public function test_la_fk_rechaza_un_laboratorio_inexistente(): void
    {
        $this->expectException(QueryException::class);

        Equipo::factory()->create(['laboratorio_id' => 999999]);
    }

    public function test_el_formulario_excluye_equipos_de_baja_y_ordena_por_codigo(): void
    {
        $laboratorio = Laboratorio::factory()->create();

        $segundo = Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-0002',
            'estado' => Equipo::ESTADO_OPERATIVO,
        ]);
        $primero = Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-0001',
            'estado' => Equipo::ESTADO_OPERATIVO,
        ]);
        Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-0000',
            'estado' => Equipo::ESTADO_DE_BAJA,
        ]);

        $respuesta = $this->get(route('mantenimientos.create'));

        $respuesta->assertOk();
        $equipos = $respuesta->viewData('equipos');
        $this->assertSame([$primero->id, $segundo->id], $equipos->pluck('id')->all());
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    private function crearEquipo(array $atributos = []): Equipo
    {
        $laboratorio = Laboratorio::factory()->create();

        return Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'fecha_adquisicion' => '2024-01-15',
            'estado' => Equipo::ESTADO_OPERATIVO,
            ...$atributos,
        ]);
    }

    /**
     * @param  array<string, mixed>  $cambios
     * @return array<string, mixed>
     */
    private function datos(Equipo $equipo, array $cambios = []): array
    {
        return [
            'equipo_id' => $equipo->id,
            'fecha' => '2024-02-01',
            'tipo' => Mantenimiento::TIPO_PREVENTIVO,
            'descripcion' => 'Revisión general de hardware.',
            'tecnico_responsable' => 'Ana Pérez',
            'costo' => '15.50',
            ...$cambios,
        ];
    }
}
