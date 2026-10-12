<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = ['manage users', 'manage roles', 'manage permissions', 'manage master data', 'create reports', 'view own reports', 'view all reports', 'verify reports', 'view dashboard', 'export reports', 'view reporter identity'];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::findOrCreate('Aparatur Kampung', 'web')->syncPermissions(['create reports', 'view own reports', 'view dashboard']);
        Role::findOrCreate('Admin Kesbangpol', 'web')->syncPermissions($permissions);
        Role::findOrCreate('Kepala Badan', 'web')->syncPermissions(['view all reports', 'view dashboard', 'export reports', 'view reporter identity']);
        Role::findOrCreate('Camat/Forkopimcam', 'web')->syncPermissions(['view all reports', 'view dashboard']);
        Role::findOrCreate('Super Admin', 'web')->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
