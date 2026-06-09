<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('barangay_id')->constrained('barangays')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('program_type', 255);
            $table->string('committee', 255);
            $table->string('program_name', 255);
            $table->unsignedInteger('participation_quantity')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('open');
            $table->text('announcement')->nullable();
            $table->json('kk_profiling_fields')->nullable();
            $table->json('custom_questions')->nullable();
            $table->timestamps();

            $table->index(['barangay_id', 'status']);
            $table->index(['barangay_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_programs');
    }
};
