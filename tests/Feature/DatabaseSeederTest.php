<?php

use App\Models\District;
use App\Models\IncidentType;
use App\Models\ReportCategory;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('the database seeder provisions Way Kanan master data and default accounts', function () {
    $this->seed(DatabaseSeeder::class);

    expect(District::count())->toBe(15);
    expect(Village::count())->toBe(227);
    expect(ReportCategory::count())->toBe(8);
    expect(IncidentType::count())->toBe(60);
    expect(User::count())->toBe(5);
});

test('every village belongs to a district and can be filtered by it', function () {
    $this->seed(DatabaseSeeder::class);

    $district = District::query()->where('code', '18.08.04')->firstOrFail();

    expect($district->villages()->count())->toBe(22);
    expect(Village::query()->where('district_id', $district->id)->where('name', 'Tiuh Balak Pasar')->exists())->toBeTrue();
    expect(Village::query()->whereNull('district_id')->exists())->toBeFalse();
});
