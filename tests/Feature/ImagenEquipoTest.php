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

class ImagenEquipoTest extends TestCase
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

    /**
     * [CRUD-CREATE] [SEGURIDAD] [VALIDACION]
     * Carga de imagen válida: guarda en carpeta equipos del disco public con ruta relativa.
     */
    public function test_carga_imagen_valida_almacena_archivo_y_guarda_ruta_relativa(): void
    {
        Storage::fake('public');
        $laboratorio = Laboratorio::factory()->create();

        $archivo = UploadedFile::fake()->image('computadora.jpg', 600, 600)->size(500);

        $respuesta = $this->post(route('equipos.store'), [
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-1001',
            'tipo' => 'computadora',
            'marca' => 'Dell',
            'modelo' => 'OptiPlex 7090',
            'numero_serie' => 'SN-DEL-1001',
            'fecha_adquisicion' => '2024-01-15',
            'costo' => '850.00',
            'estado' => 'operativo',
            'imagen' => $archivo,
        ]);

        $equipo = Equipo::where('codigo_inventario', 'LAB-1001')->firstOrFail();

        $respuesta->assertRedirect(route('equipos.show', $equipo));
        $respuesta->assertSessionHas('exito');

        $this->assertNotNull($equipo->imagen);
        $this->assertStringStartsWith('equipos/', $equipo->imagen);
        $this->assertStringNotContainsString('storage/app', $equipo->imagen);
        $this->assertStringNotContainsString('/app/', $equipo->imagen);

        Storage::disk('public')->assertExists($equipo->imagen);
    }

    /**
     * [VALIDACION]
     * Rechaza archivos que no sean imágenes en formato JPG, JPEG, PNG o WebP.
     */
    public function test_carga_imagen_rechaza_tipo_invalido(): void
    {
        Storage::fake('public');
        $laboratorio = Laboratorio::factory()->create();

        $archivoInvalido = UploadedFile::fake()->create('manual.pdf', 300, 'application/pdf');

        $respuesta = $this->from(route('equipos.create'))->post(route('equipos.store'), [
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-1002',
            'tipo' => 'computadora',
            'marca' => 'Dell',
            'modelo' => 'OptiPlex',
            'numero_serie' => 'SN-DEL-1002',
            'fecha_adquisicion' => '2024-01-15',
            'costo' => '850.00',
            'estado' => 'operativo',
            'imagen' => $archivoInvalido,
        ]);

        $respuesta->assertRedirect(route('equipos.create'));
        $respuesta->assertSessionHasErrors(['imagen']);

        $this->assertDatabaseMissing('equipos', ['codigo_inventario' => 'LAB-1002']);
    }

    /**
     * [VALIDACION]
     * Rechaza imágenes que superen el límite de 2048 KB (2 MB).
     */
    public function test_carga_imagen_rechaza_archivo_mayor_a_2_mb(): void
    {
        Storage::fake('public');
        $laboratorio = Laboratorio::factory()->create();

        // 2500 KB supera el máximo permitido de 2048 KB
        $archivoPesado = UploadedFile::fake()->image('pesada.jpg')->size(2500);

        $respuesta = $this->from(route('equipos.create'))->post(route('equipos.store'), [
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => 'LAB-1003',
            'tipo' => 'computadora',
            'marca' => 'Dell',
            'modelo' => 'OptiPlex',
            'numero_serie' => 'SN-DEL-1003',
            'fecha_adquisicion' => '2024-01-15',
            'costo' => '850.00',
            'estado' => 'operativo',
            'imagen' => $archivoPesado,
        ]);

        $respuesta->assertRedirect(route('equipos.create'));
        $respuesta->assertSessionHasErrors(['imagen']);

        $this->assertDatabaseMissing('equipos', ['codigo_inventario' => 'LAB-1003']);
    }

    /**
     * [CRUD-UPDATE]
     * Al editar sin adjuntar una nueva imagen, se conserva la ruta y el archivo actual.
     */
    public function test_edicion_conserva_imagen_actual_si_no_se_sube_nueva(): void
    {
        Storage::fake('public');
        $laboratorio = Laboratorio::factory()->create();

        $rutaOriginal = Storage::disk('public')->putFile('equipos', UploadedFile::fake()->image('original.png'));

        $equipo = Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'imagen' => $rutaOriginal,
            'marca' => 'Marca Original',
        ]);

        $respuesta = $this->put(route('equipos.update', $equipo), [
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => $equipo->codigo_inventario,
            'tipo' => $equipo->tipo,
            'marca' => 'Marca Modificada',
            'modelo' => $equipo->modelo,
            'numero_serie' => $equipo->numero_serie,
            'fecha_adquisicion' => $equipo->fecha_adquisicion->toDateString(),
            'costo' => (string) $equipo->costo,
            'estado' => $equipo->estado,
        ]);

        $respuesta->assertRedirect(route('equipos.show', $equipo));
        $respuesta->assertSessionHas('exito');

        $equipo->refresh();
        $this->assertSame('Marca Modificada', $equipo->marca);
        $this->assertSame($rutaOriginal, $equipo->imagen);
        Storage::disk('public')->assertExists($rutaOriginal);
    }

    /**
     * [CRUD-UPDATE] [REGLA-NEGOCIO]
     * Al reemplazar la imagen: almacena el nuevo archivo, actualiza la BD y elimina el anterior solo tras el éxito.
     */
    public function test_reemplazo_de_imagen_guarda_nueva_y_elimina_anterior_solo_tras_exito(): void
    {
        Storage::fake('public');
        $laboratorio = Laboratorio::factory()->create();

        $rutaVieja = Storage::disk('public')->putFile('equipos', UploadedFile::fake()->image('vieja.jpg'));

        $equipo = Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'imagen' => $rutaVieja,
        ]);

        $nuevaFoto = UploadedFile::fake()->image('nueva.png');

        $respuesta = $this->put(route('equipos.update', $equipo), [
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => $equipo->codigo_inventario,
            'tipo' => $equipo->tipo,
            'marca' => $equipo->marca,
            'modelo' => $equipo->modelo,
            'numero_serie' => $equipo->numero_serie,
            'fecha_adquisicion' => $equipo->fecha_adquisicion->toDateString(),
            'costo' => (string) $equipo->costo,
            'estado' => $equipo->estado,
            'imagen' => $nuevaFoto,
        ]);

        $respuesta->assertRedirect(route('equipos.show', $equipo));
        $respuesta->assertSessionHas('exito');

        $equipo->refresh();
        $this->assertNotSame($rutaVieja, $equipo->imagen);
        Storage::disk('public')->assertExists($equipo->imagen);
        Storage::disk('public')->assertMissing($rutaVieja);
    }

    /**
     * [CRUD-UPDATE] [REGLA-NEGOCIO]
     * Si la actualización en base de datos falla por validación de regla de negocio,
     * se compensa borrando el archivo nuevo y se conserva la imagen anterior.
     */
    public function test_reemplazo_de_imagen_compensa_borrando_nueva_si_falla_actualizacion_en_bd(): void
    {
        Storage::fake('public');
        $laboratorio = Laboratorio::factory()->create();

        $rutaOriginal = Storage::disk('public')->putFile('equipos', UploadedFile::fake()->image('foto_fija.jpg'));

        $equipo = Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'imagen' => $rutaOriginal,
            'fecha_adquisicion' => '2024-01-10',
        ]);

        // Mantenimiento en fecha 2024-02-01
        Mantenimiento::factory()->create([
            'equipo_id' => $equipo->id,
            'fecha' => '2024-02-01',
        ]);

        $nuevaFoto = UploadedFile::fake()->image('intento_fallido.png');

        // Intento con fecha de adquisición posterior al mantenimiento (inválido)
        $respuesta = $this->from(route('equipos.edit', $equipo))->put(route('equipos.update', $equipo), [
            'laboratorio_id' => $laboratorio->id,
            'codigo_inventario' => $equipo->codigo_inventario,
            'tipo' => $equipo->tipo,
            'marca' => $equipo->marca,
            'modelo' => $equipo->modelo,
            'numero_serie' => $equipo->numero_serie,
            'fecha_adquisicion' => '2024-03-01', // Posterior a mantenimiento
            'costo' => (string) $equipo->costo,
            'estado' => $equipo->estado,
            'imagen' => $nuevaFoto,
        ]);

        $respuesta->assertRedirect(route('equipos.edit', $equipo));
        $respuesta->assertSessionHasErrors(['fecha_adquisicion']);

        $equipo->refresh();
        $this->assertSame($rutaOriginal, $equipo->imagen);
        Storage::disk('public')->assertExists($rutaOriginal);

        // El disco no debe contener ningún archivo huérfano además del original
        $archivosEnDisco = Storage::disk('public')->allFiles('equipos');
        $this->assertCount(1, $archivosEnDisco);
        $this->assertSame($rutaOriginal, $archivosEnDisco[0]);
    }

    /**
     * [CRUD-DELETE] [REGLA-NEGOCIO]
     * Eliminación de equipo con historial: la base rechaza el borrado y se preserva el archivo de imagen.
     */
    public function test_eliminacion_rechazada_por_historial_conserva_archivo_de_imagen(): void
    {
        Storage::fake('public');
        $laboratorio = Laboratorio::factory()->create();

        $rutaFoto = Storage::disk('public')->putFile('equipos', UploadedFile::fake()->image('servidor.jpg'));

        $equipo = Equipo::factory()->create([
            'laboratorio_id' => $laboratorio->id,
            'imagen' => $rutaFoto,
        ]);

        Mantenimiento::factory()->create([
            'equipo_id' => $equipo->id,
            'fecha' => '2024-05-10',
        ]);

        $respuesta = $this->delete(route('equipos.destroy', $equipo));

        $respuesta->assertRedirect(route('equipos.index'));
        $respuesta->assertSessionHas('error');

        $this->assertDatabaseHas('equipos', ['id' => $equipo->id]);
        Storage::disk('public')->assertExists($rutaFoto);
    }
}
