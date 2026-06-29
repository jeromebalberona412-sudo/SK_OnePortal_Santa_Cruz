<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kabataan_registrations', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('review_notes');
            $table->string('archive_reason', 64)->nullable()->after('archived_at');
        });

        DB::statement('ALTER TABLE kabataan_registrations DROP CONSTRAINT IF EXISTS kabataan_registrations_status_check');

        DB::statement(<<<'SQL'
            ALTER TABLE kabataan_registrations ADD CONSTRAINT kabataan_registrations_status_check CHECK (
                (status)::text = ANY (
                    ARRAY[
                        'pending_verification'::text,
                        'email_verified'::text,
                        'password_set'::text,
                        'active'::text,
                        'rejected'::text,
                        'archived'::text
                    ]
                )
            )
        SQL);
    }

    public function down(): void
    {
        DB::table('kabataan_registrations')->where('status', 'archived')->update(['status' => 'active']);

        Schema::table('kabataan_registrations', function (Blueprint $table) {
            $table->dropColumn(['archived_at', 'archive_reason']);
        });

        DB::statement('ALTER TABLE kabataan_registrations DROP CONSTRAINT IF EXISTS kabataan_registrations_status_check');

        DB::statement(<<<'SQL'
            ALTER TABLE kabataan_registrations ADD CONSTRAINT kabataan_registrations_status_check CHECK (
                (status)::text = ANY (
                    ARRAY[
                        'pending_verification'::text,
                        'email_verified'::text,
                        'password_set'::text,
                        'active'::text,
                        'rejected'::text
                    ]
                )
            )
        SQL);
    }
};
