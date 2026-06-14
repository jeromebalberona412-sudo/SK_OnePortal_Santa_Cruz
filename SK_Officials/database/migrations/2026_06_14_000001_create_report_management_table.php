<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('report_management')) {
            return;
        }

        Schema::create('report_management', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('barangay_id')->constrained('barangays')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('program_code', 10);
            $table->string('program_name');
            $table->string('activity_name');
            $table->string('file_name');
            $table->string('file_path', 500);
            $table->string('file_mime', 100)->default('application/pdf');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('status', 30)->default('pending');
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['barangay_id', 'status']);
            $table->index(['program_code', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_management');
    }
};
