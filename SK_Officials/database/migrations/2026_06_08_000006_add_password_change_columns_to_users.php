<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'pending_password')) {
                $table->string('pending_password', 255)->nullable()->after('password');
            }
            if (! Schema::hasColumn('users', 'password_change_token')) {
                $table->string('password_change_token', 255)->nullable()->after('pending_password');
            }
            if (! Schema::hasColumn('users', 'password_change_token_expires_at')) {
                $table->timestamp('password_change_token_expires_at')->nullable()->after('password_change_token');
            }
            if (! Schema::hasColumn('users', 'password_change_last_sent_at')) {
                $table->timestamp('password_change_last_sent_at')->nullable()->after('password_change_token_expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'pending_password',
                'password_change_token',
                'password_change_token_expires_at',
                'password_change_last_sent_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
