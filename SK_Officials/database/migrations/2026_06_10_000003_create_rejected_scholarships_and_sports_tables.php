<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rejected_scholarships')) {
            Schema::create('rejected_scholarships', function (Blueprint $table) {
                $table->id();
                $table->foreignId('program_application_id')->unique()->constrained('program_applications')->cascadeOnDelete();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
                $table->foreignId('barangay_id')->constrained('barangays')->cascadeOnDelete();
                $table->foreignId('rejected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('rejection_reason')->nullable();
                $table->json('rejection_reasons')->nullable();
                $table->timestamp('rejected_at');
                $table->timestamp('restored_at')->nullable();
                $table->timestamps();

                $table->index(['barangay_id', 'rejected_at']);
                $table->index(['barangay_id', 'restored_at']);
            });
        }

        if (! Schema::hasTable('rejected_sports')) {
            Schema::create('rejected_sports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('program_application_id')->unique()->constrained('program_applications')->cascadeOnDelete();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
                $table->foreignId('barangay_id')->constrained('barangays')->cascadeOnDelete();
                $table->foreignId('rejected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('rejection_reason')->nullable();
                $table->json('rejection_reasons')->nullable();
                $table->timestamp('rejected_at');
                $table->timestamp('restored_at')->nullable();
                $table->timestamps();

                $table->index(['barangay_id', 'rejected_at']);
                $table->index(['barangay_id', 'restored_at']);
            });
        }

        $this->backfillRejected('rejected_scholarships', 'A');
        $this->backfillRejected('rejected_sports', 'I');
    }

    private function backfillRejected(string $table, string $letter): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasTable('program_applications') || ! Schema::hasTable('schedule_programs')) {
            return;
        }

        $existing = DB::table($table)->pluck('program_application_id')->all();

        DB::table('program_applications')
            ->join('schedule_programs', 'program_applications.program_id', '=', 'schedule_programs.id')
            ->where('program_applications.status', 'rejected')
            ->where('schedule_programs.program_letter', $letter)
            ->when($existing !== [], fn ($q) => $q->whereNotIn('program_applications.id', $existing))
            ->select([
                'program_applications.id as application_id',
                'schedule_programs.tenant_id',
                'schedule_programs.barangay_id',
                'program_applications.reviewed_by',
                'program_applications.rejection_reason',
                'program_applications.rejection_reasons',
                'program_applications.reviewed_at',
            ])
            ->orderBy('program_applications.id')
            ->chunk(200, function ($rows) use ($table) {
                $now = now();
                $inserts = [];

                foreach ($rows as $row) {
                    $inserts[] = [
                        'program_application_id' => $row->application_id,
                        'tenant_id' => $row->tenant_id,
                        'barangay_id' => $row->barangay_id,
                        'rejected_by_user_id' => $row->reviewed_by,
                        'rejection_reason' => $row->rejection_reason ?? 'Rejected',
                        'rejection_reasons' => $row->rejection_reasons,
                        'rejected_at' => $row->reviewed_at ?? $now,
                        'restored_at' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($inserts !== []) {
                    DB::table($table)->insert($inserts);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('rejected_sports');
        Schema::dropIfExists('rejected_scholarships');
    }
};
