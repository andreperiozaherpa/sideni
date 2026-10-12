<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(LazilyRefreshDatabase::class);

test('admin can create update and delete a permission', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');

    $this->actingAs($admin)->post(route('permissions.store'), ['name' => 'approve reports'])->assertRedirect();
    $permission = Permission::findByName('approve reports');

    $this->actingAs($admin)->put(route('permissions.update', $permission), ['name' => 'approve final reports'])->assertRedirect();
    expect(Permission::findByName('approve final reports'))->not->toBeNull();

    $this->actingAs($admin)->delete(route('permissions.destroy', $permission))->assertRedirect();
    expect(Permission::query()->where('name', 'approve final reports')->exists())->toBeFalse();
});

test('a permission still assigned to a role cannot be deleted', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $admin = User::factory()->create()->assignRole('Admin Kesbangpol');
    $permission = Permission::findByName('view dashboard');

    $this->actingAs($admin)->delete(route('permissions.destroy', $permission))->assertStatus(422);
    $this->assertModelExists($permission);
});

test('users without the manage permissions permission cannot manage permissions', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $aparatur = User::factory()->create()->assignRole('Aparatur Kampung');

    $this->actingAs($aparatur)->get(route('permissions.index'))->assertForbidden();
});
