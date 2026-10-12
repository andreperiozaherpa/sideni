<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(DistrictSeeder::class);
        $this->call(VillageSeeder::class);
        $this->call(ReportCategorySeeder::class);
        $this->call(IncidentTypeSeeder::class);
        $this->call(SettingSeeder::class);
    }
}
