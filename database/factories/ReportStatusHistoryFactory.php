<?php

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\ReportStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportStatusHistory>
 */
class ReportStatusHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_id' => Report::factory(),
            'changed_by_id' => User::factory(),
            'from_status' => ReportStatus::Pending,
            'to_status' => ReportStatus::Verified,
            'note' => fake()->sentence(),
        ];
    }
}
