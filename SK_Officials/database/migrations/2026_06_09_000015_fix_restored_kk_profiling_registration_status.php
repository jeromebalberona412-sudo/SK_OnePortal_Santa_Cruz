<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rejected_kk_profiling') || ! Schema::hasTable('kabataan_registrations')) {
            return;
        }

        $rows = DB::table('rejected_kk_profiling')
            ->whereNotNull('restored_at')
            ->get(['kabataan_registration_id', 'previous_registration_status', 'previous_evaluation_status']);

        foreach ($rows as $row) {
            $registration = DB::table('kabataan_registrations')
                ->where('id', $row->kabataan_registration_id)
                ->first(['id', 'status', 'evaluation_status', 'user_id']);

            if ($registration === null || $registration->status !== 'rejected') {
                continue;
            }

            $status = $row->previous_registration_status;
            if (! $status || $status === 'rejected') {
                $status = $registration->user_id ? 'active' : 'pending_verification';
            }

            $evaluation = $row->previous_evaluation_status;
            if (! $evaluation || in_array($evaluation, ['active', 'Auto Approved'], true)) {
                $evaluation = 'Not Profiled';
            }

            DB::table('kabataan_registrations')
                ->where('id', $registration->id)
                ->update([
                    'status' => $status,
                    'evaluation_status' => $evaluation,
                    'review_notes' => null,
                    'reviewed_by_user_id' => null,
                    'reviewed_at' => null,
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasTable('kk_survey_responses')) {
            $restoredIds = DB::table('rejected_kk_profiling')
                ->whereNotNull('restored_at')
                ->pluck('kabataan_registration_id');

            if ($restoredIds->isNotEmpty()) {
                DB::table('kk_survey_responses')
                    ->whereIn('kabataan_registration_id', $restoredIds)
                    ->where('status', 'rejected')
                    ->update([
                        'status' => 'pending',
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Non-reversible data repair.
    }
};
