<?php

namespace Database\Factories;

use App\Models\IncidentType;
use App\Models\ReportCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IncidentType>
 */
class IncidentTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_category_id' => ReportCategory::factory(),
            'code' => fake()->unique()->bothify('JNS-###'),
            'name' => fake()->words(2, true),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 99),
        ];
    }
}
