<?php

use App\Models\District;
use App\Models\IncidentType;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('admin can create update and delete a district', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');

    $this->actingAs($admin)->post(route('districts.store'), ['code' => '18.08.99', 'name' => 'Kecamatan Uji', 'is_active' => 1, 'sort_order' => 1])->assertRedirect();
    $district = District::where('code', '18.08.99')->firstOrFail();

    $this->actingAs($admin)->put(route('districts.update', $district), ['code' => '18.08.99', 'name' => 'Kecamatan Uji Baru', 'is_active' => 1, 'sort_order' => 2])->assertRedirect();
    expect($district->refresh()->name)->toBe('Kecamatan Uji Baru');

    $this->actingAs($admin)->delete(route('districts.destroy', $district))->assertRedirect();
    expect(District::where('code', '18.08.99')->exists())->toBeFalse();
});

test('admin can create a village under a district', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $district = District::factory()->create();

    $this->actingAs($admin)->post(route('villages.store'), ['district_id' => $district->id, 'code' => '18.08.01.2001', 'name' => 'Kampung Uji', 'is_active' => 1, 'sort_order' => 1])->assertRedirect();

    $this->assertDatabaseHas('villages', ['district_id' => $district->id, 'code' => '18.08.01.2001', 'name' => 'Kampung Uji']);
});

test('a district that still has villages cannot be deleted', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $district = District::factory()->create();
    Village::factory()->create(['district_id' => $district->id]);

    $this->actingAs($admin)->delete(route('districts.destroy', $district))->assertRedirect()->assertSessionHasErrors('district');
    $this->assertModelExists($district);
});

test('a village that still has reports cannot be deleted', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $village = Village::factory()->create();
    Report::factory()->create(['village_id' => $village->id]);

    $this->actingAs($admin)->delete(route('villages.destroy', $village))->assertRedirect()->assertSessionHasErrors('village');
    $this->assertModelExists($village);
});

test('a category still used by reports cannot be deleted', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $category = ReportCategory::factory()->create();
    Report::factory()->create(['report_category_id' => $category->id]);

    $this->actingAs($admin)->delete(route('report-categories.destroy', $category))->assertRedirect()->assertSessionHasErrors('report_category');
    $this->assertModelExists($category);
});

test('an incident type still used by reports cannot be deleted', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $category = ReportCategory::factory()->create();
    $type = IncidentType::factory()->create(['report_category_id' => $category->id]);
    Report::factory()->create(['report_category_id' => $category->id, 'incident_type_id' => $type->id]);

    $this->actingAs($admin)->delete(route('incident-types.destroy', $type))->assertRedirect()->assertSessionHasErrors('incident_type');
    $this->assertModelExists($type);
});

test('users without the master data permission cannot manage districts', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $aparatur = User::factory()->create()->assignRole('Aparatur Kampung');

    $this->actingAs($aparatur)->get(route('districts.index'))->assertForbidden();
});
