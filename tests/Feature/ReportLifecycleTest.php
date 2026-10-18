<?php

use App\Enums\ReportStatus;
use App\Models\District;
use App\Models\IncidentType;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

/**
 * @return array<string, mixed>
 */
function sideniReportPayload(Village $village, ReportCategory $category, IncidentType $type, array $overrides = []): array
{
    return array_merge([
        'village_id' => $village->id,
        'report_category_id' => $category->id,
        'incident_type_id' => $type->id,
        'reporter_name' => 'Budi Santoso',
        'reporter_phone' => '081234567890',
        'reported_at' => now()->format('Y-m-d H:i:s'),
        'location_name' => 'Jalan Lintas Way Kanan',
        'latitude' => -4.5,
        'longitude' => 104.5,
        'description' => 'Uraian kejadian awal',
        'risk_level' => 'high',
    ], $overrides);
}

test('aparatur kampung can submit a report that is registered and audited', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $village = Village::factory()->create();
    $user = User::factory()->create(['village_id' => $village->id])->assignRole('Aparatur Kampung');
    $category = ReportCategory::factory()->create();
    $type = IncidentType::factory()->create(['report_category_id' => $category->id]);

    $this->actingAs($user)->post(route('reports.store'), sideniReportPayload($village, $category, $type))
        ->assertRedirect();

    $report = Report::firstOrFail();
    expect($report->registration_number)->toBe(sprintf('SD-%d-0001', now()->year));
    expect($report->submitted_by_id)->toBe($user->id);
    expect($report->status)->toBe(ReportStatus::Pending);
    expect($report->reporter_phone)->toBe('081234567890');
    $this->assertDatabaseHas('report_status_histories', [
        'report_id' => $report->id,
        'changed_by_id' => $user->id,
        'to_status' => 'pending',
    ]);
});

test('a village-bound user only sees their own village in the report form', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $ownVillage = Village::factory()->create(['name' => 'Kampung Sendiri']);
    $otherVillage = Village::factory()->create(['name' => 'Kampung Lain']);
    $user = User::factory()->create(['village_id' => $ownVillage->id])->assignRole('Aparatur Kampung');

    $this->actingAs($user)->get(route('reports.create'))
        ->assertOk()
        ->assertSee('Kampung Sendiri')
        ->assertDontSee('Kampung Lain');

    expect($otherVillage->exists)->toBeTrue();
});

test('a village-bound user cannot submit a report for another village', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $ownVillage = Village::factory()->create();
    $otherVillage = Village::factory()->create();
    $user = User::factory()->create(['village_id' => $ownVillage->id])->assignRole('Aparatur Kampung');
    $category = ReportCategory::factory()->create();
    $type = IncidentType::factory()->create(['report_category_id' => $category->id]);

    $this->actingAs($user)
        ->post(route('reports.store'), sideniReportPayload($otherVillage, $category, $type))
        ->assertInvalid(['village_id']);

    expect(Report::query()->count())->toBe(0);
});

test('a user without a village assignment can file a report for any village', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $village = Village::factory()->create();
    $category = ReportCategory::factory()->create();
    $type = IncidentType::factory()->create(['report_category_id' => $category->id]);

    $this->actingAs($admin)
        ->post(route('reports.store'), sideniReportPayload($village, $category, $type))
        ->assertRedirect();

    expect(Report::firstOrFail()->village_id)->toBe($village->id);
});

test('a district-scoped user only sees villages from their own district in the report form', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);

    $district = District::factory()->create();
    Village::factory()->create(['district_id' => $district->id, 'name' => 'Kampung Dalam Kecamatan']);
    $otherDistrict = District::factory()->create();
    Village::factory()->create(['district_id' => $otherDistrict->id, 'name' => 'Kampung Luar Kecamatan']);

    $user = User::factory()->create(['district_id' => $district->id])->assignRole('Aparatur Kampung');

    $this->actingAs($user)->get(route('reports.create'))
        ->assertOk()
        ->assertSee('Kampung Dalam Kecamatan')
        ->assertDontSee('Kampung Luar Kecamatan');
});

test('a district-scoped user cannot submit a report for a village outside their district', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);

    $district = District::factory()->create();
    $otherDistrict = District::factory()->create();
    $outsideVillage = Village::factory()->create(['district_id' => $otherDistrict->id]);

    $user = User::factory()->create(['district_id' => $district->id])->assignRole('Aparatur Kampung');
    $category = ReportCategory::factory()->create();
    $type = IncidentType::factory()->create(['report_category_id' => $category->id]);

    $this->actingAs($user)
        ->post(route('reports.store'), sideniReportPayload($outsideVillage, $category, $type))
        ->assertInvalid(['village_id']);

    expect(Report::query()->count())->toBe(0);
});

test('a user without an assigned region cannot create a report', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create()->assignRole('Aparatur Kampung');
    $village = Village::factory()->create();
    $category = ReportCategory::factory()->create();
    $type = IncidentType::factory()->create(['report_category_id' => $category->id]);

    $this->actingAs($user)->get(route('reports.create'))->assertOk()->assertSee('belum ditetapkan wilayah');

    $this->actingAs($user)
        ->post(route('reports.store'), sideniReportPayload($village, $category, $type))
        ->assertInvalid(['village_id']);

    expect(Report::query()->count())->toBe(0);
});

test('admin can verify clarify and reject a report with a recorded actor', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $report = Report::factory()->create(['status' => 'pending']);

    $this->actingAs($admin)->post(route('reports.verify', $report))->assertRedirect();
    expect($report->refresh()->status)->toBe(ReportStatus::Verified);
    $this->assertDatabaseHas('report_status_histories', ['report_id' => $report->id, 'changed_by_id' => $admin->id, 'to_status' => 'verified']);

    $this->actingAs($admin)->post(route('reports.clarify', $report))->assertStatus(422);

    $pending = Report::factory()->create(['status' => 'pending']);
    $this->actingAs($admin)->post(route('reports.reject', $pending), [])->assertInvalid(['note']);
    $this->actingAs($admin)->post(route('reports.reject', $pending), ['note' => 'Laporan duplikat'])->assertRedirect();
    expect($pending->refresh()->status)->toBe(ReportStatus::Rejected);
});

test('admin can mark a verified report as completed', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $report = Report::factory()->create(['status' => 'verified']);

    $this->actingAs($admin)->post(route('reports.complete', $report))->assertRedirect();

    expect($report->refresh()->status)->toBe(ReportStatus::Completed);
    $this->assertDatabaseHas('report_status_histories', ['report_id' => $report->id, 'changed_by_id' => $admin->id, 'to_status' => 'completed']);
});

test('owner resubmits a clarified report and it returns to pending', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $village = Village::factory()->create();
    $owner = User::factory()->create(['village_id' => $village->id])->assignRole('Aparatur Kampung');
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $category = ReportCategory::factory()->create();
    $type = IncidentType::factory()->create(['report_category_id' => $category->id]);
    $report = Report::factory()->create([
        'submitted_by_id' => $owner->id,
        'status' => 'needs_clarification',
        'village_id' => $village->id,
        'report_category_id' => $category->id,
        'incident_type_id' => $type->id,
    ]);
    $payload = sideniReportPayload($report->village, $category, $type, ['description' => 'Uraian setelah dilengkapi']);

    $this->actingAs($owner)->get(route('reports.edit', $report))->assertOk();
    $this->actingAs($owner)->put(route('reports.update', $report), $payload)->assertRedirect();
    expect($report->refresh()->status)->toBe(ReportStatus::Pending);
    expect($report->description)->toBe('Uraian setelah dilengkapi');
    $this->assertDatabaseHas('report_status_histories', [
        'report_id' => $report->id,
        'from_status' => 'needs_clarification',
        'to_status' => 'pending',
    ]);

    $other = User::factory()->create()->assignRole('Aparatur Kampung');
    $this->actingAs($other)->get(route('reports.edit', $report))->assertForbidden();
    $this->actingAs($admin)->get(route('reports.edit', $report))->assertForbidden();
});

test('reporter identity is visible only to users with the identity permission', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $aparatur = User::factory()->create()->assignRole('Aparatur Kampung');
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $report = Report::factory()->create([
        'submitted_by_id' => $aparatur->id,
        'reporter_name' => 'Budi Santoso',
        'reporter_phone' => '081234567890',
    ]);

    expect($report->identityVisibleTo($aparatur))->toBeFalse();
    expect($report->identityVisibleTo($admin))->toBeTrue();
    expect($report->maskedReporterPhone())->toBe('0812********');

    $this->actingAs($admin)->get(route('reports.show', $report))->assertOk()->assertSee('081234567890');
    $this->actingAs($aparatur)->get(route('reports.show', $report))->assertOk()->assertDontSee('081234567890');
});

test('report attachments are stored through the global upload helper', function () {
    Storage::fake('local');
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $village = Village::factory()->create();
    $user = User::factory()->create(['village_id' => $village->id])->assignRole('Aparatur Kampung');
    $category = ReportCategory::factory()->create();
    $type = IncidentType::factory()->create(['report_category_id' => $category->id]);

    $this->actingAs($user)->post(route('reports.store'), [
        ...sideniReportPayload($village, $category, $type),
        'attachments' => [UploadedFile::fake()->image('bukti.jpg')],
    ])->assertRedirect();

    $report = Report::firstOrFail();
    expect($report->attachments)->toHaveCount(1);
    expect($report->attachments->first()->path)->toStartWith('uploads/reports/');
    Storage::disk('local')->assertExists($report->attachments->first()->path);
});
