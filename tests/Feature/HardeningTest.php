<?php

use App\Models\TemporaryUpload;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(LazilyRefreshDatabase::class);

test('the last super admin cannot be deleted', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $superAdmin = User::factory()->create()->assignRole('Super Admin');

    $this->actingAs($admin)->delete(route('users.destroy', $superAdmin))->assertRedirect()->assertSessionHasErrors('user');
    $this->assertModelExists($superAdmin);
});

test('a user cannot delete their own account', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');

    $this->actingAs($admin)->delete(route('users.destroy', $admin))->assertRedirect()->assertSessionHasErrors('user');
    $this->assertModelExists($admin);
});

test('the temporary upload pruner removes only stale uploads', function () {
    Storage::fake('local');
    $user = User::factory()->create();

    $stale = $user->temporaryUploads()->create([
        'token' => (string) Str::uuid(),
        'disk' => 'local',
        'path' => 'uploads/tmp/old.jpg',
        'original_name' => 'old.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 100,
    ]);
    $stale->forceFill(['created_at' => now()->subDays(2)])->save();
    Storage::disk('local')->put('uploads/tmp/old.jpg', 'stale');

    $fresh = $user->temporaryUploads()->create([
        'token' => (string) Str::uuid(),
        'disk' => 'local',
        'path' => 'uploads/tmp/new.jpg',
        'original_name' => 'new.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 100,
    ]);
    Storage::disk('local')->put('uploads/tmp/new.jpg', 'fresh');

    $this->artisan('uploads:prune')->assertSuccessful();

    expect(TemporaryUpload::query()->whereKey($stale->id)->exists())->toBeFalse();
    expect(TemporaryUpload::query()->whereKey($fresh->id)->exists())->toBeTrue();
    Storage::disk('local')->assertMissing('uploads/tmp/old.jpg');
    Storage::disk('local')->assertExists('uploads/tmp/new.jpg');
});
