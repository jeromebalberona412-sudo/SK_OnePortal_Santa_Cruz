<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('kk_profiling_schedules')
            ->whereIn('status', ['Upcoming', 'Rescheduled'])
            ->update(['status' => 'Ongoing']);

        DB::table('kk_profiling_schedules')
            ->where('status', 'Cancelled')
            ->update(['status' => 'Close']);

        DB::statement('ALTER TABLE kk_profiling_schedules DROP CONSTRAINT IF EXISTS kk_profiling_schedules_status_check');
        DB::statement("ALTER TABLE kk_profiling_schedules ADD CONSTRAINT kk_profiling_schedules_status_check CHECK ((status)::text = ANY (ARRAY['Ongoing'::text, 'Completed'::text, 'Close'::text]))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE kk_profiling_schedules DROP CONSTRAINT IF EXISTS kk_profiling_schedules_status_check');
        DB::statement("ALTER TABLE kk_profiling_schedules ADD CONSTRAINT kk_profiling_schedules_status_check CHECK ((status)::text = ANY (ARRAY['Upcoming'::text, 'Ongoing'::text, 'Completed'::text, 'Cancelled'::text, 'Rescheduled'::text]))");
    }
};
