<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VacationSpot>
 */
class VacationSpotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->city(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude()
        ];
    }
}
