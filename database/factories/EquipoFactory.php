<?php

namespace Database\Factories;

use App\Models\Equipo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * @extends Factory<Equipo>
 */
class EquipoFactory extends Factory
{
    protected $model = Equipo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'laboratorio_id' => fn (): int => DB::table('laboratorios')->inRandomOrder()->value('id')
                ?? throw new LogicException('Primero ejecute LaboratorioSeeder.'),
            'codigo_inventario' => fake()->unique()->numerify('LAB-####'),
            'tipo' => fake()->randomElement(['computadora', 'proyector', 'impresora', 'switch']),
            'marca' => fake()->randomElement(['Dell', 'HP', 'Lenovo', 'Epson', 'Cisco']),
            'modelo' => fake()->bothify('Modelo-??-###'),
            'numero_serie' => fake()->unique()->bothify('SER-????????-########'),
            'fecha_adquisicion' => fake()->dateTimeBetween('-5 years', 'yesterday')->format('Y-m-d'),
            'costo' => fake()->randomFloat(2, 0, 5000),
            'estado' => fake()->randomElement(['operativo', 'en_reparacion', 'de_baja']),
            'imagen' => null,
        ];
    }
}
