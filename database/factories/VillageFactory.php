<?php

namespace Database\Factories;

use App\Models\District;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Village>
 */
class VillageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'district_id' => District::factory(),
            'code' => fake()->unique()->bothify('KPG-####'),
            'name' => fake()->citySuffix(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 99),
        ];
    }
}
