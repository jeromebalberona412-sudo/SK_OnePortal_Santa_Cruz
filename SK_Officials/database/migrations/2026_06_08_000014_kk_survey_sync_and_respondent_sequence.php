<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('kk_survey_responses')) {
            Schema::create('kk_survey_responses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('barangay_id')->constrained('barangays')->cascadeOnDelete();
                $table->foreignId('kabataan_registration_id')->unique()->constrained('kabataan_registrations')->cascadeOnDelete();
                $table->string('respondent_number', 50)->nullable();
                $table->date('survey_date')->nullable();
                $table->string('last_name', 100);
                $table->string('first_name', 100);
                $table->string('middle_name', 100)->nullable();
                $table->string('suffix', 50)->nullable();
                $table->string('region', 100)->nullable();
                $table->string('province', 100)->nullable();
                $table->string('municipality', 100)->nullable();
                $table->string('barangay', 100)->nullable();
                $table->string('purok_zone', 100)->nullable();
                $table->string('sex_assigned_at_birth', 20)->nullable();
                $table->integer('age')->nullable();
                $table->date('birthdate')->nullable();
                $table->string('email', 255)->nullable();
                $table->string('contact_number', 20)->nullable();
                $table->string('civil_status', 50)->nullable();
                $table->string('youth_age_group', 50)->nullable();
                $table->string('educational_background', 100)->nullable();
                $table->string('youth_classification', 100)->nullable();
                $table->string('work_status', 100)->nullable();
                $table->boolean('registered_sk_voter')->default(false);
                $table->boolean('registered_national_voter')->default(false);
                $table->boolean('attended_kk_assembly')->default(false);
                $table->boolean('voted_last_sk')->default(false);
                $table->string('kk_assembly_attendance_count')->nullable();
                $table->text('kk_assembly_non_attendance_reason')->nullable();
                $table->string('facebook_account')->nullable();
                $table->boolean('willing_to_join_group_chat')->default(false);
                $table->text('participant_signature')->nullable();
                $table->boolean('consent_given')->default(true);
                $table->string('status', 30)->default('pending');
                $table->timestamps();

                $table->index(['barangay_id', 'status']);
                $table->index(['barangay_id', 'survey_date']);
            });
        }

        if (Schema::hasTable('rejected_kk_profiling')) {
            Schema::table('rejected_kk_profiling', function (Blueprint $table) {
                if (! Schema::hasColumn('rejected_kk_profiling', 'previous_registration_status')) {
                    $table->string('previous_registration_status', 50)->nullable()->after('rejection_reason');
                }
                if (! Schema::hasColumn('rejected_kk_profiling', 'previous_evaluation_status')) {
                    $table->string('previous_evaluation_status', 50)->nullable()->after('previous_registration_status');
                }
                if (! Schema::hasColumn('rejected_kk_profiling', 'previous_user_status')) {
                    $table->string('previous_user_status', 50)->nullable()->after('previous_evaluation_status');
                }
            });
        }

        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_respondent_number(
                p_tenant_id BIGINT,
                p_barangay_id BIGINT
            )
            RETURNS TEXT
            LANGUAGE plpgsql
            AS \$\$
            DECLARE
                current_year TEXT;
                next_seq INTEGER;
                barangay_prefix TEXT;
            BEGIN
                current_year := EXTRACT(YEAR FROM CURRENT_DATE)::TEXT;

                SELECT UPPER(LEFT(REGEXP_REPLACE(name, '[^a-zA-Z0-9]', '', 'g'), 8))
                INTO barangay_prefix
                FROM barangays
                WHERE id = p_barangay_id;

                IF barangay_prefix IS NULL OR barangay_prefix = '' THEN
                    barangay_prefix := 'BRGY';
                END IF;

                SELECT COALESCE(MAX(respondent_sequence), 0) + 1
                INTO next_seq
                FROM kabataan_registrations
                WHERE tenant_id = p_tenant_id
                  AND barangay_id = p_barangay_id
                  AND EXTRACT(YEAR FROM COALESCE(submitted_at, created_at)) =
                      EXTRACT(YEAR FROM CURRENT_DATE);

                RETURN barangay_prefix || '-' || current_year || '-' || LPAD(next_seq::TEXT, 4, '0');
            END;
            \$\$;
        ");

        $this->backfillRespondentNumbers();
    }

    public function down(): void
    {
        if (Schema::hasTable('rejected_kk_profiling')) {
            Schema::table('rejected_kk_profiling', function (Blueprint $table) {
                if (Schema::hasColumn('rejected_kk_profiling', 'previous_user_status')) {
                    $table->dropColumn('previous_user_status');
                }
                if (Schema::hasColumn('rejected_kk_profiling', 'previous_evaluation_status')) {
                    $table->dropColumn('previous_evaluation_status');
                }
                if (Schema::hasColumn('rejected_kk_profiling', 'previous_registration_status')) {
                    $table->dropColumn('previous_registration_status');
                }
            });
        }
    }

    private function backfillRespondentNumbers(): void
    {
        if (! Schema::hasTable('kabataan_registrations')) {
            return;
        }

        $rows = DB::table('kabataan_registrations')
            ->whereNull('respondent_number')
            ->whereNotNull('submitted_at')
            ->orderBy('barangay_id')
            ->orderBy('submitted_at')
            ->orderBy('id')
            ->get(['id', 'tenant_id', 'barangay_id']);

        foreach ($rows as $row) {
            if (! $row->tenant_id || ! $row->barangay_id) {
                continue;
            }

            $generated = DB::selectOne(
                'SELECT generate_respondent_number(?, ?) AS respondent_number',
                [$row->tenant_id, $row->barangay_id]
            );

            $respondentNumber = $generated->respondent_number ?? null;
            if (! $respondentNumber) {
                continue;
            }

            $sequence = (int) substr($respondentNumber, strrpos($respondentNumber, '-') + 1);

            DB::table('kabataan_registrations')
                ->where('id', $row->id)
                ->update([
                    'respondent_number' => $respondentNumber,
                    'respondent_sequence' => $sequence,
                    'updated_at' => now(),
                ]);
        }
    }
};
