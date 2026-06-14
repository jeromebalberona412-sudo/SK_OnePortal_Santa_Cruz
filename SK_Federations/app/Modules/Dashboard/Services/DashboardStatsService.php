<?php

namespace App\Modules\Dashboard\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardStatsService
{
    public function totalKabataanRegistered(): int
    {
        if (! Schema::hasTable('kabataan_registrations')) {
            return 0;
        }

        $query = DB::table('kabataan_registrations');

        if (Schema::hasColumn('kabataan_registrations', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if (Schema::hasColumn('kabataan_registrations', 'status')) {
            $query->whereIn('status', ['active', 'email_verified', 'password_set']);
        }

        return (int) $query->count();
    }
}
