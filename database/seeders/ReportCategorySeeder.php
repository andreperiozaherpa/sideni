<?php

namespace Database\Seeders;

use App\Models\ReportCategory;
use Illuminate\Database\Seeder;

class ReportCategorySeeder extends Seeder
{
    /**
     * Seed the classification of social conflict vulnerabilities.
     *
     * @var array<int, array{code: string, name: string}>
     */
    private array $categories = [
        ['code' => 'TANAH', 'name' => 'Pertanahan'],
        ['code' => 'TIBUM', 'name' => 'Ketertiban Umum'],
        ['code' => 'SDA', 'name' => 'Sumber Daya Alam'],
        ['code' => 'EKON', 'name' => 'Ekonomi & Ketenagakerjaan'],
        ['code' => 'POL', 'name' => 'Politik & Pemerintahan'],
        ['code' => 'SOSBUD', 'name' => 'Sosial & Budaya'],
        ['code' => 'KAMTIB', 'name' => 'Keamanan & Kriminalitas'],
        ['code' => 'AGAMA', 'name' => 'Agama & Kepercayaan'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->categories as $index => $category) {
            ReportCategory::query()->updateOrCreate(
                ['code' => $category['code']],
                [
                    'name' => $category['name'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}
