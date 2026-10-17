<?php

namespace Database\Factories;

use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Oscilloscope', 'Multimeter', 'Solder Station', 'Power Supply', 'Logic Analyzer']),
            'code' => strtoupper(fake()->unique()->bothify('ALT-####')),
            'category' => fake()->randomElement(['Elektronika', 'Jaringan', 'Ukur']),
            'condition' => fake()->randomElement(['baik', 'rusak ringan', 'rusak berat']),
            'quantity' => fake()->numberBetween(1, 20),
        ];
    }
}
