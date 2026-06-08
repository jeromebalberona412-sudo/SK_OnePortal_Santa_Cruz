<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rejected_kk_profiling')) {
            Schema::create('rejected_kk_profiling', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kabataan_registration_id')->unique()->constrained('kabataan_registrations')->cascadeOnDelete();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
                $table->foreignId('barangay_id')->constrained('barangays')->cascadeOnDelete();
                $table->foreignId('rejected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('rejection_reason');
                $table->timestamp('rejected_at');
                $table->timestamp('restored_at')->nullable();
                $table->timestamps();

                $table->index(['barangay_id', 'rejected_at']);
                $table->index(['barangay_id', 'restored_at']);
            });
        }

        if (Schema::hasTable('rejected_kk_profiling') && Schema::hasTable('kabataan_registrations')) {
            $existing = DB::table('rejected_kk_profiling')->pluck('kabataan_registration_id')->all();

            DB::table('kabataan_registrations')
                ->where('status', 'rejected')
                ->when($existing !== [], fn ($q) => $q->whereNotIn('id', $existing))
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    $now = now();
                    $inserts = [];

                    foreach ($rows as $row) {
                        $inserts[] = [
                            'kabataan_registration_id' => $row->id,
                            'tenant_id' => $row->tenant_id,
                            'barangay_id' => $row->barangay_id,
                            'rejected_by_user_id' => $row->reviewed_by_user_id,
                            'rejection_reason' => $row->review_notes ?? 'Rejected',
                            'rejected_at' => $row->reviewed_at ?? $now,
                            'restored_at' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    if ($inserts !== []) {
                        DB::table('rejected_kk_profiling')->insert($inserts);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rejected_kk_profiling');
    }
};
