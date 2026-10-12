<?php

namespace Database\Factories;

use App\Models\ReportCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportCategory>
 */
class ReportCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('CAT-###'),
            'name' => fake()->unique()->words(2, true),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 99),
        ];
    }
}
