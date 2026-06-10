<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_programs', function (Blueprint $table) {
            if (! Schema::hasColumn('schedule_programs', 'is_archived')) {
                $table->boolean('is_archived')->default(false)->after('status');
            }
            if (! Schema::hasColumn('schedule_programs', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('is_archived');
            }
            if (! Schema::hasColumn('schedule_programs', 'archived_by')) {
                $table->foreignId('archived_by')->nullable()->after('archived_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('schedule_programs', 'deleted_reason')) {
                $table->text('deleted_reason')->nullable()->after('archived_by');
            }
            if (! Schema::hasColumn('schedule_programs', 'restored_at')) {
                $table->timestamp('restored_at')->nullable()->after('deleted_reason');
            }
            if (! Schema::hasColumn('schedule_programs', 'restored_by')) {
                $table->foreignId('restored_by')->nullable()->after('restored_at')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('schedule_programs', function (Blueprint $table) {
            $table->index(['barangay_id', 'program_letter', 'is_archived'], 'schedule_programs_barangay_letter_archived_index');
            $table->index(['is_archived', 'archived_at'], 'schedule_programs_archived_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('schedule_programs', function (Blueprint $table) {
            $table->dropIndex('schedule_programs_barangay_letter_archived_index');
            $table->dropIndex('schedule_programs_archived_at_index');
        });

        Schema::table('schedule_programs', function (Blueprint $table) {
            $columns = ['restored_by', 'restored_at', 'deleted_reason', 'archived_by', 'archived_at', 'is_archived'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('schedule_programs', $column)) {
                    if (in_array($column, ['archived_by', 'restored_by'], true)) {
                        $table->dropForeign(['schedule_programs_'.$column.'_foreign']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
