<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MantenimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ejemplos = [
            ['codigo' => 'LAB-0001', 'tipo' => 'preventivo', 'descripcion' => 'Ejemplo: limpieza y revisión general.', 'costo' => '15.00'],
            ['codigo' => 'LAB-0002', 'tipo' => 'correctivo', 'descripcion' => 'Ejemplo: diagnóstico y reparación de alimentación.', 'costo' => '45.50'],
            ['codigo' => 'LAB-0004', 'tipo' => 'preventivo', 'descripcion' => 'Ejemplo: verificación de conexiones y funcionamiento.', 'costo' => '0.00'],
        ];

        foreach ($ejemplos as $ejemplo) {
            $equipo = DB::table('equipos')->where('codigo_inventario', $ejemplo['codigo'])->lockForUpdate()->first();

            if ($equipo === null || $equipo->estado === 'de_baja') {
                continue;
            }

            $clave = ['equipo_id' => $equipo->id, 'descripcion' => $ejemplo['descripcion']];

            if (DB::table('mantenimientos')->where($clave)->exists()) {
                continue;
            }

            $fecha = Carbon::parse($equipo->fecha_adquisicion)->max(today()->subDays(30));

            DB::table('mantenimientos')->insert([
                ...$clave,
                'fecha' => $fecha->toDateString(),
                'tipo' => $ejemplo['tipo'],
                'tecnico_responsable' => 'Técnico de prueba',
                'costo' => $ejemplo['costo'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($ejemplo['tipo'] === 'correctivo') { // [REGLA-NEGOCIO]
                DB::table('equipos')->where('id', $equipo->id)->update([
                    'estado' => 'en_reparacion',
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
