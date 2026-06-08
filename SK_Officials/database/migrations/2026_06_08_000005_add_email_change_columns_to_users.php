<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'pending_email')) {
                $table->string('pending_email', 255)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'email_change_token')) {
                $table->string('email_change_token', 255)->nullable()->after('pending_email');
            }
            if (! Schema::hasColumn('users', 'email_change_token_expires_at')) {
                $table->timestamp('email_change_token_expires_at')->nullable()->after('email_change_token');
            }
            if (! Schema::hasColumn('users', 'email_change_verified_at')) {
                $table->timestamp('email_change_verified_at')->nullable()->after('email_change_token_expires_at');
            }
            if (! Schema::hasColumn('users', 'email_change_last_sent_at')) {
                $table->timestamp('email_change_last_sent_at')->nullable()->after('email_change_verified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'pending_email',
                'email_change_token',
                'email_change_token_expires_at',
                'email_change_verified_at',
                'email_change_last_sent_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
