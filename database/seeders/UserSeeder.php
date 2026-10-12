<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed default accounts for each role (password: "password").
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Super Admin', 'username' => 'superadmin', 'email' => 'superadmin@sideni.test', 'role' => 'Super Admin'],
            ['name' => 'Admin Kesbangpol', 'username' => 'admin', 'email' => 'admin@sideni.test', 'role' => 'Admin Kesbangpol'],
            ['name' => 'Kepala Badan', 'username' => 'kepala', 'email' => 'kepala@sideni.test', 'role' => 'Kepala Badan'],
            ['name' => 'Camat / Forkopimcam', 'username' => 'camat', 'email' => 'camat@sideni.test', 'role' => 'Camat/Forkopimcam'],
            ['name' => 'Aparatur Kampung', 'username' => 'kampung', 'email' => 'kampung@sideni.test', 'role' => 'Aparatur Kampung'],
        ];

        foreach ($users as $data) {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'username' => $data['username'], 'password' => 'password'],
            );

            $user->syncRoles([$data['role']]);
        }
    }
}
