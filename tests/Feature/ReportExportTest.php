<?php

use App\Models\District;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('an authorised user can download a csv of reports filtered by district', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');

    $districtA = District::factory()->create();
    $districtB = District::factory()->create();
    $villageA = Village::factory()->create(['district_id' => $districtA->id]);
    $villageB = Village::factory()->create(['district_id' => $districtB->id]);

    $included = Report::factory()->create(['village_id' => $villageA->id]);
    $excluded = Report::factory()->create(['village_id' => $villageB->id]);

    $response = $this->actingAs($admin)->get(route('reports.export.download', [
        'format' => 'csv',
        'district_id' => $districtA->id,
    ]));

    $response->assertOk()->assertDownload();

    $content = file_get_contents($response->baseResponse->getFile()->getPathname());

    expect($content)->toContain($included->registration_number);
    expect($content)->not->toContain($excluded->registration_number);
});

test('the export honours the category and date range filters', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');

    $categoryA = ReportCategory::factory()->create();
    $categoryB = ReportCategory::factory()->create();

    $inRange = Report::factory()->create(['report_category_id' => $categoryA->id, 'created_at' => now()->subDays(3)]);
    $tooOld = Report::factory()->create(['report_category_id' => $categoryA->id, 'created_at' => now()->subDays(40)]);
    $otherCategory = Report::factory()->create(['report_category_id' => $categoryB->id, 'created_at' => now()->subDays(3)]);

    $response = $this->actingAs($admin)->get(route('reports.export.download', [
        'format' => 'csv',
        'report_category_id' => $categoryA->id,
        'date_from' => now()->subDays(7)->format('Y-m-d'),
        'date_to' => now()->format('Y-m-d'),
    ]));

    $response->assertOk();

    $content = file_get_contents($response->baseResponse->getFile()->getPathname());

    expect($content)->toContain($inRange->registration_number);
    expect($content)->not->toContain($tooOld->registration_number);
    expect($content)->not->toContain($otherCategory->registration_number);
});

test('the export can be produced as an xlsx file', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    Report::factory()->create();

    $this->actingAs($admin)
        ->get(route('reports.export.download', ['format' => 'xlsx']))
        ->assertOk()
        ->assertDownload();
});

test('the export form is available to users with the export permission', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');

    $this->actingAs($admin)->get(route('reports.export'))
        ->assertOk()
        ->assertSee('Export Laporan')
        ->assertSee('Kecamatan')
        ->assertSee('Dari tanggal');
});

test('users without the export permission cannot export reports', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $aparatur = User::factory()->create()->assignRole('Aparatur Kampung');

    $this->actingAs($aparatur)->get(route('reports.export'))->assertForbidden();
    $this->actingAs($aparatur)->get(route('reports.export.download', ['format' => 'csv']))->assertForbidden();
});

test('a village-scoped user only exports reports from their own village', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);

    $district = District::factory()->create();
    $ownVillage = Village::factory()->create(['district_id' => $district->id, 'name' => 'Kampung Sendiri']);
    $otherVillage = Village::factory()->create(['district_id' => $district->id, 'name' => 'Kampung Lain']);

    $user = User::factory()->create(['village_id' => $ownVillage->id]);
    $user->givePermissionTo('export reports');

    $included = Report::factory()->create(['village_id' => $ownVillage->id]);
    $excluded = Report::factory()->create(['village_id' => $otherVillage->id]);

    $response = $this->actingAs($user)->get(route('reports.export.download', [
        'format' => 'csv',
        'district_id' => $district->id,
    ]));

    $response->assertOk();

    $content = file_get_contents($response->baseResponse->getFile()->getPathname());

    expect($content)->toContain($included->registration_number);
    expect($content)->not->toContain($excluded->registration_number);
});

test('a district-scoped user only exports reports from their own district', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);

    $districtA = District::factory()->create();
    $districtB = District::factory()->create();
    $villageA = Village::factory()->create(['district_id' => $districtA->id]);
    $villageB = Village::factory()->create(['district_id' => $districtB->id]);

    $user = User::factory()->create(['district_id' => $districtA->id]);
    $user->givePermissionTo('export reports');

    $included = Report::factory()->create(['village_id' => $villageA->id]);
    $excluded = Report::factory()->create(['village_id' => $villageB->id]);

    $response = $this->actingAs($user)->get(route('reports.export.download', ['format' => 'csv']));

    $response->assertOk();

    $content = file_get_contents($response->baseResponse->getFile()->getPathname());

    expect($content)->toContain($included->registration_number);
    expect($content)->not->toContain($excluded->registration_number);
});

test('a user without an assigned region cannot export', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $user->givePermissionTo('export reports');

    $this->actingAs($user)->get(route('reports.export.download', ['format' => 'csv']))
        ->assertRedirect(route('reports.export'))
        ->assertSessionHasErrors('region');
});

test('the export form warns a region-scoped user about their scope', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);

    $village = Village::factory()->create();
    $user = User::factory()->create(['village_id' => $village->id]);
    $user->givePermissionTo('export reports');

    $this->actingAs($user)->get(route('reports.export'))
        ->assertOk()
        ->assertSee('Export dibatasi ke wilayah Anda');
});
