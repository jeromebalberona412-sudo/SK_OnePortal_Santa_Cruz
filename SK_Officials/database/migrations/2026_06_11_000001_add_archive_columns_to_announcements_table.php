<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('announcements')) {
            return;
        }

        Schema::table('announcements', function (Blueprint $table) {
            if (! Schema::hasColumn('announcements', 'is_archived')) {
                $table->boolean('is_archived')->default(false)->after('is_federation_wide');
            }

            if (! Schema::hasColumn('announcements', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('is_archived');
            }

            if (! Schema::hasColumn('announcements', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->after('archived_at');
            }
        });

        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'is_archived')) {
                $table->index(['barangay_id', 'is_archived', 'archived_at'], 'announcements_barangay_archive_idx');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('announcements')) {
            return;
        }

        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'is_archived')) {
                $table->dropIndex('announcements_barangay_archive_idx');
            }
        });

        Schema::table('announcements', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('announcements', 'deleted_at') ? 'deleted_at' : null,
                Schema::hasColumn('announcements', 'archived_at') ? 'archived_at' : null,
                Schema::hasColumn('announcements', 'is_archived') ? 'is_archived' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
