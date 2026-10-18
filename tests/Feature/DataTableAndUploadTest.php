<?php

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

test('the reports index serves server-side datatables json', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    Report::factory()->count(2)->create();

    $response = $this->actingAs($admin)->getJson(route('reports.index'), ['X-Requested-With' => 'XMLHttpRequest']);

    $response->assertOk()->assertJsonStructure(['draw', 'data', 'recordsTotal', 'recordsFiltered']);
    expect($response->json('recordsTotal'))->toBe(2);
});

test('the master data index serves server-side datatables json', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    District::factory()->count(3)->create();

    $response = $this->actingAs($admin)->getJson(route('districts.index'), ['X-Requested-With' => 'XMLHttpRequest']);

    $response->assertOk()->assertJsonStructure(['draw', 'data', 'recordsTotal', 'recordsFiltered']);
    expect($response->json('recordsTotal'))->toBe(3);
});

test('the datatables endpoint applies per-column search', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    District::factory()->create(['name' => 'Kecamatan Alfa']);
    District::factory()->create(['name' => 'Kecamatan Beta']);

    $column = fn (string $data, bool $searchable) => [
        'data' => $data,
        'name' => $data,
        'searchable' => $searchable ? 'true' : 'false',
        'orderable' => $searchable ? 'true' : 'false',
        'search' => ['value' => '', 'regex' => 'false'],
    ];

    $query = [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'search' => ['value' => '', 'regex' => 'false'],
        'order' => [['column' => 0, 'dir' => 'asc']],
        'columns' => [
            $column('code', true),
            array_merge($column('name', true), ['search' => ['value' => 'Beta', 'regex' => 'false']]),
            $column('villages_count', false),
            $column('status_label', false),
            $column('action', false),
        ],
    ];

    $response = $this->actingAs($admin)->getJson(route('districts.index', $query), ['X-Requested-With' => 'XMLHttpRequest']);

    $response->assertOk();
    expect($response->json('recordsTotal'))->toBe(2);
    expect($response->json('recordsFiltered'))->toBe(1);
});

test('the reports datatable searches across derived columns', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');

    $village = Village::factory()->create(['name' => 'Kampung CariUji']);
    Report::factory()->create(['village_id' => $village->id]);
    Report::factory()->count(2)->create();

    $column = fn (string $data, bool $searchable) => [
        'data' => $data,
        'name' => $data,
        'searchable' => $searchable ? 'true' : 'false',
        'orderable' => 'false',
        'search' => ['value' => '', 'regex' => 'false'],
    ];

    $query = [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'search' => ['value' => 'CariUji', 'regex' => 'false'],
        'order' => [['column' => 0, 'dir' => 'desc']],
        'columns' => [
            $column('registration_number', true),
            $column('village_name', true),
            $column('category_name', true),
            $column('risk_label', true),
            $column('status_label', true),
            $column('created_label', false),
            $column('sla', false),
            $column('action', false),
        ],
    ];

    $response = $this->actingAs($admin)->getJson(route('reports.index', $query), ['X-Requested-With' => 'XMLHttpRequest']);

    $response->assertOk();
    expect($response->json('recordsTotal'))->toBe(3);
    expect($response->json('recordsFiltered'))->toBe(1);
});

test('the reports datatable renders coloured status badges', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    Report::factory()->create(['status' => 'rejected']);

    $response = $this->actingAs($admin)->getJson(route('reports.index'), ['X-Requested-With' => 'XMLHttpRequest']);

    $response->assertOk()->assertJsonFragment([
        'status_label' => '<span class="badge bg-rose-100 text-rose-800">Rejected</span>',
    ]);
});

test('a temporary upload can be stored and discarded', function () {
    Storage::fake('local');
    $user = User::factory()->create();

    $store = $this->actingAs($user)->post(
        route('uploads.temporary.store'),
        ['file' => UploadedFile::fake()->image('bukti.jpg')],
        ['Accept' => 'application/json'],
    );
    $store->assertOk()->assertJsonStructure(['token', 'name', 'size']);
    $token = $store->json('token');
    $this->assertDatabaseHas('temporary_uploads', ['token' => $token, 'user_id' => $user->id]);

    $this->actingAs($user)->delete(route('uploads.temporary.destroy', $token), [], ['Accept' => 'application/json'])->assertOk();
    $this->assertDatabaseMissing('temporary_uploads', ['token' => $token]);
});

test('temporary uploads are moved into report attachments on submit', function () {
    Storage::fake('local');
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::factory()->create()->assignRole('Aparatur Kampung');
    $village = Village::factory()->create();
    $category = ReportCategory::factory()->create();
    $type = IncidentType::factory()->create(['report_category_id' => $category->id]);

    $token = $this->actingAs($user)->post(
        route('uploads.temporary.store'),
        ['file' => UploadedFile::fake()->image('bukti.jpg')],
        ['Accept' => 'application/json'],
    )->json('token');

    $this->actingAs($user)->post(route('reports.store'), [
        'village_id' => $village->id,
        'report_category_id' => $category->id,
        'incident_type_id' => $type->id,
        'reporter_name' => 'Budi Santoso',
        'reporter_phone' => '081234567890',
        'reported_at' => now()->format('Y-m-d H:i:s'),
        'location_name' => 'Jalan Lintas',
        'latitude' => -4.5,
        'longitude' => 104.5,
        'description' => 'Uraian kejadian',
        'risk_level' => 'high',
        'uploaded_files' => [$token],
    ])->assertRedirect();

    $report = Report::firstOrFail();
    expect($report->attachments)->toHaveCount(1);
    expect($report->attachments->first()->path)->toStartWith('uploads/reports/');
    Storage::disk('local')->assertExists($report->attachments->first()->path);
    $this->assertDatabaseMissing('temporary_uploads', ['token' => $token]);
});
