<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('barangay_id')->constrained('barangays')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('program_letter', 5)->default('A');
            $table->foreignId('schedule_program_id')->nullable()->constrained('schedule_programs')->nullOnDelete();
            $table->string('title', 255);
            $table->text('instructions')->nullable();
            $table->json('custom_questions')->nullable();
            $table->string('status', 20)->default('draft');
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->index(['barangay_id', 'program_letter']);
            $table->index(['barangay_id', 'status']);
            $table->index(['schedule_program_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_evaluations');
    }
};
