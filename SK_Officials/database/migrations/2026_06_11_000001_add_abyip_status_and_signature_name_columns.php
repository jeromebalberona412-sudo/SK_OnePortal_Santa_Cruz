<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('abyip')) {
            return;
        }

        Schema::table('abyip', function (Blueprint $table) {
            if (! Schema::hasColumn('abyip', 'status')) {
                $table->string('status', 30)->nullable()->after('approved_by_user_id');
            }

            if (! Schema::hasColumn('abyip', 'prepared_by_name')) {
                $table->string('prepared_by_name', 255)->nullable()->after('status');
            }

            if (! Schema::hasColumn('abyip', 'prepared_by_position')) {
                $table->string('prepared_by_position', 255)->nullable()->after('prepared_by_name');
            }

            if (! Schema::hasColumn('abyip', 'approved_by_name')) {
                $table->string('approved_by_name', 255)->nullable()->after('prepared_by_position');
            }

            if (! Schema::hasColumn('abyip', 'approved_by_position')) {
                $table->string('approved_by_position', 255)->nullable()->after('approved_by_name');
            }
        });

        DB::table('abyip')
            ->where('row_type', 'document')
            ->whereNull('status')
            ->update(['status' => 'pending']);

        DB::statement("
            UPDATE abyip
            SET prepared_by_name = prepared_by,
                prepared_by_position = prepared_position,
                approved_by_name = approved_by,
                approved_by_position = approved_position
            WHERE row_type = 'document'
              AND prepared_by_name IS NULL
              AND prepared_by IS NOT NULL
        ");
    }

    public function down(): void
    {
        if (! Schema::hasTable('abyip')) {
            return;
        }

        Schema::table('abyip', function (Blueprint $table) {
            foreach (['approved_by_position', 'approved_by_name', 'prepared_by_position', 'prepared_by_name', 'status'] as $column) {
                if (Schema::hasColumn('abyip', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
