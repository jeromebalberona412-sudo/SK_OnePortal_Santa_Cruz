<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('lockout_count')->default(0)->after('remember_token');
            $table->timestamp('lockout_until')->nullable()->after('lockout_count');
            $table->timestamp('last_login_at')->nullable()->after('lockout_until');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->index('lockout_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['lockout_until']);
            $table->dropColumn(['lockout_count', 'lockout_until', 'last_login_at', 'last_login_ip']);
        });
    }
};
