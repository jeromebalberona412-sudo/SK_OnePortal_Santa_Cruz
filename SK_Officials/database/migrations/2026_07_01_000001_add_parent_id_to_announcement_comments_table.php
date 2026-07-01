<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('announcement_comments')) {
            return;
        }

        Schema::table('announcement_comments', function (Blueprint $table) {
            if (! Schema::hasColumn('announcement_comments', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('announcement_id');
                $table->index(['announcement_id', 'parent_id'], 'announcement_comments_parent_idx');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('announcement_comments')) {
            return;
        }

        Schema::table('announcement_comments', function (Blueprint $table) {
            if (Schema::hasColumn('announcement_comments', 'parent_id')) {
                $table->dropIndex('announcement_comments_parent_idx');
                $table->dropColumn('parent_id');
            }
        });
    }
};
