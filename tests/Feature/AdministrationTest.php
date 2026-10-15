<?php

use App\Models\User;
use App\Models\Village;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(LazilyRefreshDatabase::class);

test('a user can update their own profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'Nama Baru',
        'username' => 'namabaru',
        'email' => 'namabaru@sideni.test',
    ])->assertRedirect();

    expect($user->refresh()->name)->toBe('Nama Baru');
    expect($user->username)->toBe('namabaru');
});

test('a user can change their own password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'username' => $user->username,
        'email' => $user->email,
        'password' => 'password-baru-123',
        'password_confirmation' => 'password-baru-123',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(Hash::check('password-baru-123', $user->refresh()->password))->toBeTrue();
});

test('an admin can assign a village to a user', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $village = Village::factory()->create();
    $role = Role::findByName('Aparatur Kampung');

    $this->actingAs($admin)->post(route('users.store'), [
        'name' => 'Petugas Kampung',
        'username' => 'petugaskampung',
        'email' => 'petugas@sideni.test',
        'password' => 'password-panjang-123',
        'password_confirmation' => 'password-panjang-123',
        'village_id' => $village->id,
        'roles' => [$role->id],
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(User::query()->where('username', 'petugaskampung')->firstOrFail()->village_id)->toBe($village->id);
});

test('role management is restricted to privileged users and can assign permissions', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $aparatur = User::factory()->create()->assignRole('Aparatur Kampung');
    $permission = Permission::findByName('view dashboard');

    $this->actingAs($aparatur)->get(route('roles.index'))->assertForbidden();

    $this->actingAs($admin)->post(route('roles.store'), ['name' => 'Role Uji', 'permissions' => [$permission->id]])->assertRedirect();
    $role = Role::findByName('Role Uji');
    expect($role->hasPermissionTo('view dashboard'))->toBeTrue();

    $this->actingAs($admin)->delete(route('roles.destroy', $role))->assertRedirect();
    expect(Role::query()->where('name', 'Role Uji')->exists())->toBeFalse();
});

test('a role still held by a user cannot be deleted', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $role = Role::findByName('Camat/Forkopimcam');
    User::factory()->create()->assignRole('Camat/Forkopimcam');

    $this->actingAs($admin)->delete(route('roles.destroy', $role))->assertStatus(422);
    $this->assertModelExists($role);
});

test('a user can upload a profile avatar', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'username' => $user->username,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($user->refresh()->avatar_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar_path);
});
