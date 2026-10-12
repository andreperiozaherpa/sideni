<?php

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Enums\RiskLevel;
use App\Models\IncidentType;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use App\Models\Village;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_number' => fake()->unique()->bothify('SD-2026-####'),
            'submitted_by_id' => User::factory(),
            'village_id' => Village::factory(),
            'report_category_id' => ReportCategory::factory(),
            'incident_type_id' => IncidentType::factory(),
            'reporter_name' => fake()->name(),
            'reporter_phone' => fake()->numerify('08##########'),
            'reported_at' => now(),
            'occurred_at' => now()->subHour(),
            'location_name' => fake()->address(),
            'latitude' => '-4.5000000',
            'longitude' => '104.5000000',
            'description' => fake()->paragraph(),
            'risk_level' => RiskLevel::Medium,
            'status' => ReportStatus::Pending,
        ];
    }
}
