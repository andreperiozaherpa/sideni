<?php

use App\Models\District;
use App\Models\IncidentType;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(LazilyRefreshDatabase::class);

test('every main page renders for a super admin', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Super Admin');

    $urls = [
        route('dashboard'),
        route('reports.index'),
        route('reports.create'),
        route('notifications.index'),
        route('report-categories.index'),
        route('report-categories.create'),
        route('incident-types.index'),
        route('incident-types.create'),
        route('districts.index'),
        route('districts.create'),
        route('villages.index'),
        route('villages.create'),
        route('settings.edit'),
        route('users.index'),
        route('users.create'),
        route('roles.index'),
        route('roles.create'),
        route('permissions.index'),
        route('permissions.create'),
        route('profile.edit'),
    ];

    foreach ($urls as $url) {
        $this->actingAs($admin)->get($url)->assertOk();
    }
});

test('the layout exposes SEO metadata and the Way Kanan favicon', function () {
    $this->withoutVite();

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('name="description"', false)
        ->assertSee('rel="canonical"', false)
        ->assertSee('favicon-32x32.png', false)
        ->assertSee('apple-touch-icon.png', false)
        ->assertSee('og-image.png', false)
        ->assertSee('site.webmanifest', false);
});

test('the sidebar renders collapsible menu groups', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Super Admin');

    $this->actingAs($admin)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-nav-group', false)
        ->assertSee('data-submenu-toggle', false)
        ->assertSee('Data Master');
});

test('the report form exposes the dropzone upload field', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::factory()->create()->assignRole('Aparatur Kampung');

    $this->actingAs($user)->get(route('reports.create'))
        ->assertOk()
        ->assertSee('data-dropzone', false)
        ->assertSee('data-dropzone-input', false);
});

test('the report form wires the dependent incident type dropdown to select2', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::factory()->create()->assignRole('Aparatur Kampung');

    $this->actingAs($user)->get(route('reports.create'))
        ->assertOk()
        ->assertSee('select2:select', false);
});

test('the verification pages render only for verifiers', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $aparatur = User::factory()->create()->assignRole('Aparatur Kampung');
    $report = Report::factory()->create(['status' => 'pending']);

    $this->actingAs($admin)->get(route('reports.verify.index'))->assertOk();
    $this->actingAs($admin)->get(route('reports.verify.show', $report))->assertOk()->assertSee($report->registration_number)->assertSee('Tindak Lanjut')->assertSee('Saran Tindakan');

    $this->actingAs($aparatur)->get(route('reports.verify.index'))->assertForbidden();
    $this->actingAs($aparatur)->get(route('reports.verify.show', $report))->assertForbidden();
});

test('master data and access edit pages render', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Super Admin');
    $district = District::factory()->create();
    $village = Village::factory()->create(['district_id' => $district->id]);
    $category = ReportCategory::factory()->create();
    $type = IncidentType::factory()->create(['report_category_id' => $category->id]);

    $urls = [
        route('districts.edit', $district),
        route('villages.edit', $village),
        route('report-categories.edit', $category),
        route('incident-types.edit', $type),
        route('users.edit', $admin),
        route('roles.edit', Role::findByName('Admin Kesbangpol')),
        route('permissions.edit', Permission::findByName('view dashboard')),
    ];

    foreach ($urls as $url) {
        $this->actingAs($admin)->get($url)->assertOk();
    }
});
