<?php

use App\Models\Report;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('the dashboard summarises only the reports an aparatur kampung may see', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $aparatur = User::factory()->create()->assignRole('Aparatur Kampung');
    $other = User::factory()->create()->assignRole('Aparatur Kampung');
    Report::factory()->create(['submitted_by_id' => $aparatur->id, 'status' => 'pending']);
    Report::factory()->create(['submitted_by_id' => $other->id, 'status' => 'completed']);

    $response = $this->actingAs($aparatur)->get(route('dashboard'));

    $response->assertOk()->assertSee('Total laporan');
    expect($response->viewData('stats')['total'])->toBe(1);
    expect($response->viewData('stats')['pending'])->toBe(1);
    expect($response->viewData('stats')['completed'])->toBe(0);
    expect($response->viewData('mapPoints'))->toHaveCount(1);
});

test('the dashboard shows every report to an admin', function () {
    $this->withoutVite();
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    Report::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get(route('dashboard'));

    $response->assertOk();
    expect($response->viewData('stats')['total'])->toBe(3);
    expect($response->viewData('mapPoints'))->toHaveCount(3);
});
