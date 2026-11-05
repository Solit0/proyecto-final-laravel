<?php

namespace Database\Factories;

use App\Models\Equipo;
use App\Models\Laboratorio;
use App\Models\Mantenimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mantenimiento>
 */
class MantenimientoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'equipo_id' => Equipo::factory()->state([
                'laboratorio_id' => Laboratorio::factory(),
            ]),
            'fecha' => today()->toDateString(),
            'tipo' => fake()->randomElement(Mantenimiento::TIPOS),
            'descripcion' => fake()->sentence(12),
            'tecnico_responsable' => fake()->name(),
            'costo' => fake()->randomFloat(2, 0, 500),
        ];
    }
}
