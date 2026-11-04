<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaboratorioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ahora = now();

        DB::table('laboratorios')->insertOrIgnore([
            ['nombre' => 'Laboratorio de Programación', 'ubicacion' => 'Edificio A, aula 101', 'capacidad' => 30, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['nombre' => 'Laboratorio de Redes', 'ubicacion' => 'Edificio A, aula 102', 'capacidad' => 25, 'created_at' => $ahora, 'updated_at' => $ahora],
            ['nombre' => 'Laboratorio de Hardware', 'ubicacion' => 'Edificio B, aula 201', 'capacidad' => 20, 'created_at' => $ahora, 'updated_at' => $ahora],
        ]);
    }
}
