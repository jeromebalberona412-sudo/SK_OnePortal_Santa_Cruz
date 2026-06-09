<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sk_official_trusted_devices')) {
            return;
        }

        Schema::table('sk_official_trusted_devices', function (Blueprint $table): void {
            if (! Schema::hasColumn('sk_official_trusted_devices', 'device_token_hash')) {
                $table->string('device_token_hash', 64)->nullable()->after('fingerprint');
            }

            if (! Schema::hasColumn('sk_official_trusted_devices', 'last_used_at')) {
                $table->timestamp('last_used_at')->nullable()->after('ip_address');
            }

            if (! Schema::hasColumn('sk_official_trusted_devices', 'metadata')) {
                $table->json('metadata')->nullable()->after('expires_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sk_official_trusted_devices')) {
            return;
        }

        Schema::table('sk_official_trusted_devices', function (Blueprint $table): void {
            foreach (['device_token_hash', 'last_used_at', 'metadata'] as $column) {
                if (Schema::hasColumn('sk_official_trusted_devices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
