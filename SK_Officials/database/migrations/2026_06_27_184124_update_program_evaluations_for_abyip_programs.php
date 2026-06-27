<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_evaluations', function (Blueprint $table) {
            if (! Schema::hasColumn('program_evaluations', 'abyip_program_id')) {
                $table->unsignedBigInteger('abyip_program_id')->nullable()->after('schedule_program_id');
                $table->foreign('abyip_program_id')->references('id')->on('abyip')->nullOnDelete();
            }

            if (! Schema::hasColumn('program_evaluations', 'start_date')) {
                $table->date('start_date')->nullable()->after('status');
            }

            if (! Schema::hasColumn('program_evaluations', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });

        DB::table('program_evaluations')
            ->whereIn('status', ['draft', 'active'])
            ->update(['status' => 'open']);

        DB::statement(
            'CREATE UNIQUE INDEX IF NOT EXISTS program_evaluations_barangay_program_year_unique '
            .'ON program_evaluations (barangay_id, abyip_program_id, (EXTRACT(YEAR FROM start_date))) '
            .'WHERE abyip_program_id IS NOT NULL AND start_date IS NOT NULL'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS program_evaluations_barangay_program_year_unique');

        Schema::table('program_evaluations', function (Blueprint $table) {
            if (Schema::hasColumn('program_evaluations', 'abyip_program_id')) {
                $table->dropForeign(['abyip_program_id']);
                $table->dropColumn('abyip_program_id');
            }

            if (Schema::hasColumn('program_evaluations', 'start_date')) {
                $table->dropColumn('start_date');
            }

            if (Schema::hasColumn('program_evaluations', 'end_date')) {
                $table->dropColumn('end_date');
            }
        });
    }
};
