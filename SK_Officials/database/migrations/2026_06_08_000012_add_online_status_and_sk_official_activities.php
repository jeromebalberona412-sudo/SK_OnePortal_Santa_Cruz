<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'online_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('online_status', 20)->default('offline')->after('last_seen');
            });
        }

        if (! Schema::hasTable('sk_official_activities')) {
            Schema::create('sk_official_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
                $table->foreignId('barangay_id')->constrained('barangays')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('action', 80);
                $table->string('description', 500);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['barangay_id', 'created_at']);
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sk_official_activities');

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'online_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('online_status');
            });
        }
    }
};
