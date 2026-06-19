<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DefaultAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Bootstrap super admin credentials now live in SK_Federations only.
        // Run: php artisan skfed:ensure-bootstrap-admin --reset-password
    }
}
