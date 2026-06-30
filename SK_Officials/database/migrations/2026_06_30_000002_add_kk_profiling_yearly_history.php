<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kk_profiling_schedules') && ! Schema::hasColumn('kk_profiling_schedules', 'profiling_year')) {
            Schema::table('kk_profiling_schedules', function (Blueprint $table) {
                $table->unsignedSmallInteger('profiling_year')->nullable()->after('barangay_id');
            });

            DB::table('kk_profiling_schedules')
                ->whereNull('profiling_year')
                ->orderBy('id')
                ->each(function ($row) {
                    $year = (int) date('Y', strtotime((string) $row->date_start)) + 1;
                    DB::table('kk_profiling_schedules')
                        ->where('id', $row->id)
                        ->update(['profiling_year' => $year]);
                });
        }

        if (! Schema::hasTable('kabataan_profiling_history')) {
            Schema::create('kabataan_profiling_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kabataan_registration_id')->constrained('kabataan_registrations')->cascadeOnDelete();
                $table->unsignedSmallInteger('profiling_year');
                $table->foreignId('kk_profiling_schedule_id')->nullable()->constrained('kk_profiling_schedules')->nullOnDelete();
                $table->json('form_data');
                $table->string('last_name');
                $table->string('first_name');
                $table->string('middle_name')->nullable();
                $table->string('suffix')->nullable();
                $table->string('email');
                $table->string('contact_number')->nullable();
                $table->timestamp('submitted_at');
                $table->timestamps();

                $table->unique(['kabataan_registration_id', 'profiling_year'], 'kab_prof_hist_reg_year_unique');
                $table->index('profiling_year');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kabataan_profiling_history');

        if (Schema::hasTable('kk_profiling_schedules') && Schema::hasColumn('kk_profiling_schedules', 'profiling_year')) {
            Schema::table('kk_profiling_schedules', function (Blueprint $table) {
                $table->dropColumn('profiling_year');
            });
        }
    }
};
