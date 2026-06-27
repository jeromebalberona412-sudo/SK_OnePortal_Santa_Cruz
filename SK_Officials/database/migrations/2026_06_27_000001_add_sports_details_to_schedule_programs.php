<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_programs', function (Blueprint $table) {
            $table->json('sports_details')->nullable()->after('scholarship_details');
        });
    }

    public function down(): void
    {
        Schema::table('schedule_programs', function (Blueprint $table) {
            $table->dropColumn('sports_details');
        });
    }
};
