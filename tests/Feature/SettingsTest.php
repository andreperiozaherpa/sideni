<?php

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

test('admin can update the institution identity and signing official', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');

    $this->actingAs($admin)->put(route('settings.update'), [
        'institution_name' => 'Bakesbangpol Way Kanan',
        'institution_address' => 'Blambangan Umpu',
        'institution_phone' => '0723-123456',
        'institution_email' => 'kesbangpol@waykanan.go.id',
        'head_name' => 'Dr. Contoh',
        'head_nip' => '19700101 199001 1 001',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(Setting::get('institution_name'))->toBe('Bakesbangpol Way Kanan');
    expect(Setting::get('head_name'))->toBe('Dr. Contoh');
});

test('admin can upload an institution logo', function () {
    Storage::fake('public');
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');

    $this->actingAs($admin)->put(route('settings.update'), [
        'institution_name' => 'Bakesbangpol Way Kanan',
        'institution_logo' => UploadedFile::fake()->image('logo.png'),
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(Setting::get('institution_logo'))->not->toBeNull();
    Storage::disk('public')->assertExists(Setting::get('institution_logo'));
});

test('users without the master data permission cannot open settings', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $aparatur = User::factory()->create()->assignRole('Aparatur Kampung');

    $this->actingAs($aparatur)->get(route('settings.edit'))->assertForbidden();
});
