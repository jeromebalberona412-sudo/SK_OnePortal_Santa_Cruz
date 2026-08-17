<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('kk_profiling_schedules')) {
            return;
        }

        Schema::table('kk_profiling_schedules', function (Blueprint $table) {
            if (! Schema::hasColumn('kk_profiling_schedules', 'allow_existing_update')) {
                $table->boolean('allow_existing_update')->default(false)->after('status');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('kk_profiling_schedules')) {
            return;
        }

        Schema::table('kk_profiling_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('kk_profiling_schedules', 'allow_existing_update')) {
                $table->dropColumn('allow_existing_update');
            }
        });
    }
};
