<?php

use App\Models\Report;
use App\Models\User;
use App\Notifications\ReportStatusChanged;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

uses(LazilyRefreshDatabase::class);

test('the submitter is notified when the report status changes', function () {
    Notification::fake();
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $submitter = User::factory()->create()->assignRole('Aparatur Kampung');
    $report = Report::factory()->create(['submitted_by_id' => $submitter->id, 'status' => 'pending']);

    $this->actingAs($admin)->post(route('reports.verify', $report))->assertRedirect();

    Notification::assertSentTo($submitter, ReportStatusChanged::class);
});

test('a status change queues the notification instead of sending it inline', function () {
    Queue::fake();
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $submitter = User::factory()->create()->assignRole('Aparatur Kampung');
    $report = Report::factory()->create(['submitted_by_id' => $submitter->id, 'status' => 'pending']);

    $this->actingAs($admin)->post(route('reports.verify', $report))->assertRedirect();

    Queue::assertPushed(SendQueuedNotifications::class);
});

test('a user can view notifications and mark them as read', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::factory()->create()->assignRole('Aparatur Kampung');
    $report = Report::factory()->create(['submitted_by_id' => $user->id]);
    $user->notify(new ReportStatusChanged($report));

    $this->actingAs($user)->get(route('notifications.index'))->assertOk()->assertSee($report->registration_number);
    expect($user->refresh()->unreadNotifications)->toHaveCount(1);

    $notification = $user->notifications()->first();
    $this->actingAs($user)->post(route('notifications.read', $notification->id))->assertRedirect(route('reports.show', $report));
    expect($user->refresh()->unreadNotifications)->toHaveCount(0);
});

test('notification emails show the Way Kanan emblem instead of the Laravel logo', function () {
    $report = Report::factory()->create(['status' => 'pending']);
    $user = User::factory()->create();

    $message = (new ReportStatusChanged($report))->toMail($user);
    $html = (string) app(Markdown::class)->render('notifications::email', $message->data());

    expect($html)->toContain('Lambang_Kabupaten_Way_Kanan.png');
    expect(str_contains($html, 'laravel.com'))->toBeFalse();
});
