<?php

namespace Database\Seeders;

use App\Modules\Shared\Models\User;
use Illuminate\Database\Seeder;
class DefaultAdminSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'skoneportal@gmail.com')->exists()) {
            return;
        }

        User::create([
            'name' => 'System Administrator',
            'email' => 'skoneportal@gmail.com',
            'password' => '@Jerome123456',
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'must_change_password' => true,
            'email_verified_at' => now(),
        ]);
    }
}
