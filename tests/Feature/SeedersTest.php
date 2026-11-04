<?php

namespace Tests\Feature;

use Database\Factories\EquipoFactory;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LogicException;
use RuntimeException;
use Tests\TestCase;

class SeedersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            throw new LogicException('Estas pruebas requieren SQLite en memoria; no deben modificar Supabase.');
        }

        $migracion = require base_path('tests/Fixtures/migrations/2026_11_03_000000_create_seed_contract_tables.php');
        $migracion->up();
    }

    public function test_seeders_generate_the_required_inventory_and_consistent_history(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('laboratorios', 3);
        $this->assertDatabaseCount('equipos', 15);
        $this->assertDatabaseCount('mantenimientos', 3);
        $this->assertSame(3, DB::table('equipos')->distinct()->count('laboratorio_id'));
        $this->assertSame(4, DB::table('equipos')->distinct()->count('tipo'));
        $this->assertSame(3, DB::table('equipos')->distinct()->count('estado'));
        $this->assertSame(0, DB::table('equipos')->whereNotNull('imagen')->count());
        $this->assertSame(0, DB::table('equipos')->whereDate('fecha_adquisicion', '>', today())->count());

        foreach (DB::table('mantenimientos')->get() as $mantenimiento) {
            $equipo = DB::table('equipos')->find($mantenimiento->equipo_id);
            $this->assertGreaterThanOrEqual($equipo->fecha_adquisicion, $mantenimiento->fecha);
            $this->assertNotSame('de_baja', $equipo->estado);

            if ($mantenimiento->tipo === 'correctivo') {
                $this->assertSame('en_reparacion', $equipo->estado);
            }
        }
    }

    public function test_repeating_seeders_does_not_duplicate_or_overwrite_existing_equipment(): void
    {
        $this->seed(DatabaseSeeder::class);
        DB::table('equipos')->where('codigo_inventario', 'LAB-0001')->update(['marca' => 'Marca editada por el equipo']);
        DB::table('equipos')->where('codigo_inventario', 'LAB-0002')->update(['estado' => 'operativo']);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('laboratorios', 3);
        $this->assertDatabaseCount('equipos', 15);
        $this->assertDatabaseCount('mantenimientos', 3);
        $this->assertDatabaseHas('equipos', ['codigo_inventario' => 'LAB-0001', 'marca' => 'Marca editada por el equipo']);
        $this->assertDatabaseHas('equipos', ['codigo_inventario' => 'LAB-0002', 'estado' => 'operativo']);
    }

    public function test_factory_generates_valid_unique_identifiers_without_a_model_dependency(): void
    {
        $equipos = EquipoFactory::new()->count(100)->raw(['laboratorio_id' => 1]);

        $this->assertCount(100, array_unique(array_column($equipos, 'codigo_inventario')));
        $this->assertCount(100, array_unique(array_column($equipos, 'numero_serie')));

        foreach ($equipos as $equipo) {
            $this->assertMatchesRegularExpression('/^LAB-[0-9]{4}$/', $equipo['codigo_inventario']);
            $this->assertGreaterThanOrEqual(0, $equipo['costo']);
            $this->assertLessThanOrEqual(today()->toDateString(), $equipo['fecha_adquisicion']);
        }
    }

    public function test_missing_business_migrations_are_reported_before_inserting_any_data(): void
    {
        Schema::drop('mantenimientos');

        try {
            $this->seed(DatabaseSeeder::class);
            $this->fail('Debía informar que faltan las migraciones de mantenimientos.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Falta la tabla mantenimientos', $exception->getMessage());
            $this->assertDatabaseCount('laboratorios', 0);
            $this->assertDatabaseCount('equipos', 0);
        }
    }

    public function test_a_seed_failure_rolls_back_previously_inserted_catalogs(): void
    {
        Schema::table('equipos', function (Blueprint $table): void {
            $table->dropColumn('marca');
        });

        try {
            $this->seed(DatabaseSeeder::class);
            $this->fail('Debía fallar porque el esquema no cumple el contrato.');
        } catch (QueryException) {
            $this->assertDatabaseCount('laboratorios', 0);
            $this->assertDatabaseCount('equipos', 0);
        }
    }

    public function test_seeders_do_not_add_maintenance_to_equipment_retired_after_the_first_run(): void
    {
        $this->seed(DatabaseSeeder::class);
        $equipo = DB::table('equipos')->where('codigo_inventario', 'LAB-0004')->first();
        DB::table('mantenimientos')->where('equipo_id', $equipo->id)->delete();
        DB::table('equipos')->where('id', $equipo->id)->update(['estado' => 'de_baja']);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseMissing('mantenimientos', ['equipo_id' => $equipo->id]);
        $this->assertDatabaseHas('equipos', ['id' => $equipo->id, 'estado' => 'de_baja']);
    }
}
