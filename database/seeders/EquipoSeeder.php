<?php

namespace Database\Seeders;

use Database\Factories\EquipoFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $laboratorios = DB::table('laboratorios')->orderBy('id')->limit(3)->pluck('id')->all();
        $tipos = ['computadora', 'proyector', 'impresora', 'switch'];
        $estados = ['operativo', 'en_reparacion', 'de_baja'];

        for ($indice = 0; $indice < 15; $indice++) {
            $codigo = sprintf('LAB-%04d', $indice + 1);

            // Conservar los datos que un compañero ya editó en la base compartida.
            if (DB::table('equipos')->where('codigo_inventario', $codigo)->exists()) {
                continue;
            }

            $datos = EquipoFactory::new()->raw([
                'laboratorio_id' => $laboratorios[$indice % count($laboratorios)],
                'codigo_inventario' => $codigo,
                'numero_serie' => sprintf('UNICAES-DEMO-%04d', $indice + 1),
                'tipo' => $tipos[$indice % count($tipos)],
                'estado' => $estados[$indice % count($estados)],
                'costo' => $indice === 0 ? 0 : fake()->randomFloat(2, 1, 5000),
            ]);

            DB::table('equipos')->insert([
                ...$datos,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
