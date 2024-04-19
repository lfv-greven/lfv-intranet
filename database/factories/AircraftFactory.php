<?php

namespace Database\Factories;

use App\Enums\OilLevelType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Aircraft>
 */
class AircraftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration' => $this->faker->lexify('D-E???'),
            'oil_level_type' => $this->faker->randomElement(OilLevelType::cases()),
            'owned' => $this->faker->boolean(80),
        ];
    }
}
