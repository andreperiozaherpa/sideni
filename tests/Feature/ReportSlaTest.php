<?php

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('a report past its stage deadline is marked overdue', function () {
    $report = Report::factory()->create(['status' => 'pending']);
    $report->forceFill(['status_changed_at' => now()->subHours(2)])->save();

    expect($report->slaState())->toBe('overdue');
    expect($report->slaDeadline())->not->toBeNull();
});

test('a freshly created report is within its sla', function () {
    $report = Report::factory()->create(['status' => 'pending']);

    expect($report->slaState())->toBe('ok');
});

test('a terminal report has no active sla', function () {
    $report = Report::factory()->create(['status' => 'rejected']);

    expect($report->slaState())->toBeNull();
    expect($report->slaDeadline())->toBeNull();
});

test('changing the status refreshes the stage timestamp', function () {
    $report = Report::factory()->create(['status' => 'pending']);
    $report->forceFill(['status_changed_at' => now()->subDay()])->save();

    $report->status = ReportStatus::Verified;
    $report->save();

    expect($report->status_changed_at->greaterThan(now()->subMinute()))->toBeTrue();
});

test('the reports datatable exposes the sla indicator', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $report = Report::factory()->create(['status' => 'pending']);
    $report->forceFill(['status_changed_at' => now()->subHours(2)])->save();

    $response = $this->actingAs($admin)->getJson(route('reports.index'), ['X-Requested-With' => 'XMLHttpRequest']);

    $response->assertOk()->assertJsonFragment(['sla' => '<span class="badge bg-rose-100 text-rose-700">Terlambat</span>']);
});
