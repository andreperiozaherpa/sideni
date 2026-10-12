<?php

use App\Mail\ReportVerifiedMail;
use App\Models\Report;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(LazilyRefreshDatabase::class);

test('the outgoing letter number follows the 300/sequence/V.06-WK/year format', function () {
    $report = Report::factory()->create(['registration_number' => 'SD-2026-0012']);

    expect($report->letterNumber())->toBe('300/0012/V.06-WK/'.now()->year);
});

test('the head of agency can download the regent detection report pdf once verified', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $head = User::factory()->create()->assignRole('Kepala Badan');
    $report = Report::factory()->create(['status' => 'verified']);

    $response = $this->actingAs($head)->get(route('reports.pdf.bupati', $report));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

test('documents are not available before a report is verified', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $report = Report::factory()->create(['status' => 'pending']);

    $this->actingAs($admin)->get(route('reports.pdf.bupati', $report))->assertForbidden();
    $this->actingAs($admin)->get(route('reports.pdf.notice', $report))->assertForbidden();
});

test('aparatur kampung cannot print the regent report but can print their own notice', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $aparatur = User::factory()->create()->assignRole('Aparatur Kampung');
    $report = Report::factory()->create(['submitted_by_id' => $aparatur->id, 'status' => 'verified']);

    $this->actingAs($aparatur)->get(route('reports.pdf.bupati', $report))->assertForbidden();
    $this->actingAs($aparatur)->get(route('reports.pdf.notice', $report))->assertOk();
});

test('verifying a report queues the notice email to the submitter', function () {
    Mail::fake();
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $submitter = User::factory()->create()->assignRole('Aparatur Kampung');
    $report = Report::factory()->create(['submitted_by_id' => $submitter->id, 'status' => 'pending']);

    $this->actingAs($admin)->post(route('reports.verify', $report), [
        'follow_up' => 'Koordinasi awal dengan aparat kampung.',
        'recommendation' => 'Tingkatkan pemantauan wilayah.',
    ])->assertRedirect();

    $report->refresh();
    expect($report->follow_up)->toBe('Koordinasi awal dengan aparat kampung.');
    expect($report->recommendation)->toBe('Tingkatkan pemantauan wilayah.');

    Mail::assertQueued(ReportVerifiedMail::class, fn (ReportVerifiedMail $mail) => $mail->hasTo($submitter->email));
});
