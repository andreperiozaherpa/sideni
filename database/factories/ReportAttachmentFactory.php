<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\ReportAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportAttachment>
 */
class ReportAttachmentFactory extends Factory
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
            'disk' => 'local',
            'path' => 'reports/'.fake()->uuid().'.jpg',
            'original_name' => 'evidence.jpg',
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(10_000, 5_000_000),
        ];
    }
}
