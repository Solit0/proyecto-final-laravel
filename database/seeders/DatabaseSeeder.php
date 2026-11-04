<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (['laboratorios', 'equipos', 'mantenimientos'] as $tabla) {
            if (! Schema::hasTable($tabla)) {
                throw new RuntimeException("Falta la tabla {$tabla}. Integre las migraciones de Miguel y ejecute php artisan migrate antes de sembrar.");
            }
        }

        DB::transaction(function (): void {
            $this->call([
                LaboratorioSeeder::class,
                EquipoSeeder::class,
                MantenimientoSeeder::class,
            ]);
        });
    }
}
