<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('program_applications')) {
            return;
        }

        Schema::create('program_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('barangay_id')->constrained('barangays')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kabataan_registration_id')->nullable()->constrained('kabataan_registrations')->nullOnDelete();
            $table->foreignId('schedule_program_id')->constrained('schedule_programs')->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'schedule_program_id']);
            $table->index(['barangay_id', 'status']);
        });

        Schema::create('program_application_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_application_id')->constrained('program_applications')->cascadeOnDelete();
            $table->string('question_id', 100);
            $table->string('question_label', 500)->nullable();
            $table->string('question_type', 50)->nullable();
            $table->text('answer_text')->nullable();
            $table->timestamps();

            $table->index(['program_application_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_application_answers');
        Schema::dropIfExists('program_applications');
    }
};
