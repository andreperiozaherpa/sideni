<?php

use App\Enums\ReportStatus;
use App\Enums\RiskLevel;
use App\Models\Report;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('stores a report with its sensitive identity hidden from serialization', function () {
    $report = Report::factory()->create([
        'reporter_phone' => '081234567890',
        'risk_level' => RiskLevel::High,
        'status' => ReportStatus::Pending,
    ]);

    $this->assertModelExists($report);

    $report->refresh();

    expect($report->reporter_phone)->toBe('081234567890');
    expect($report->risk_level)->toBe(RiskLevel::High);
    expect($report->status)->toBe(ReportStatus::Pending);
    expect($report->toArray())->not->toHaveKey('reporter_phone');
});

test('relates a report to its master data and submitting user', function () {
    $report = Report::factory()->create();

    $report->load('submittedBy', 'village.district', 'reportCategory', 'incidentType.reportCategory');

    expect($report->submittedBy)->not->toBeNull();
    expect($report->village->district)->not->toBeNull();
    expect($report->reportCategory)->not->toBeNull();
    expect($report->incidentType->reportCategory)->not->toBeNull();
});
