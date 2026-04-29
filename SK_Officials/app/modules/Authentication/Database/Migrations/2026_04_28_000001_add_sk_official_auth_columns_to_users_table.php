<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            }

            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 30)->default('user')->after('password');
            }

            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status', 30)->default('PENDING_APPROVAL')->after('role');
            }

            if (! Schema::hasColumn('users', 'barangay_id')) {
                $table->unsignedBigInteger('barangay_id')->nullable()->after('status');
            }

            if (! Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('barangay_id');
            }

            if (! Schema::hasColumn('users', 'lockout_count')) {
                $table->unsignedInteger('lockout_count')->default(0)->after('remember_token');
            }

            if (! Schema::hasColumn('users', 'lockout_until')) {
                $table->timestamp('lockout_until')->nullable()->after('lockout_count');
            }

            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('lockout_until');
            }

            if (! Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            }

            if (! Schema::hasColumn('users', 'active_session_id')) {
                $table->string('active_session_id', 255)->nullable()->after('last_login_ip');
            }

            if (! Schema::hasColumn('users', 'last_seen')) {
                $table->timestamp('last_seen')->nullable()->after('active_session_id');
            }

            if (! Schema::hasColumn('users', 'active_device')) {
                $table->string('active_device', 255)->nullable()->after('last_seen');
            }

            if (! Schema::hasColumn('users', 'last_ip')) {
                $table->string('last_ip', 45)->nullable()->after('active_device');
            }

            if (! Schema::hasColumn('users', 'otp_code')) {
                $table->string('otp_code', 255)->nullable()->after('last_ip');
            }

            if (! Schema::hasColumn('users', 'otp_expires_at')) {
                $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            }

            if (! Schema::hasColumn('users', 'otp_attempts')) {
                $table->unsignedTinyInteger('otp_attempts')->default(0)->after('otp_expires_at');
            }

            if (! Schema::hasColumn('users', 'otp_last_sent_at')) {
                $table->timestamp('otp_last_sent_at')->nullable()->after('otp_attempts');
            }

            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        $tenantId = DB::table('tenants')->where('code', (string) config('sk_official_auth.tenant_code', 'santa_cruz'))->value('id');

        if ($tenantId) {
            DB::table('users')->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
        }

        $this->createIndexIfMissing('users', 'users_role_index', ['role']);
        $this->createIndexIfMissing('users', 'users_status_index', ['status']);
        $this->createIndexIfMissing('users', 'users_tenant_id_index', ['tenant_id']);
        $this->createIndexIfMissing('users', 'users_barangay_id_index', ['barangay_id']);
        $this->createIndexIfMissing('users', 'users_lockout_until_index', ['lockout_until']);
    }

    public function down(): void
    {
        // This migration intentionally keeps shared account columns on rollback.
    }

    /**
     * @param  array<int, string>  $columns
     */
    protected function createIndexIfMissing(string $table, string $indexName, array $columns): void
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return;
            }
        }

        if (DB::getDriverName() === 'pgsql') {
            $exists = DB::table('pg_indexes')
                ->where('tablename', $table)
                ->where('indexname', $indexName)
                ->whereRaw('schemaname = current_schema()')
                ->exists();

            if ($exists) {
                return;
            }
        }

        try {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($columns, $indexName) {
                $tableBlueprint->index($columns, $indexName);
            });
        } catch (\Throwable) {
            // Existing installations may already have the index under a driver-generated name.
        }
    }
};
