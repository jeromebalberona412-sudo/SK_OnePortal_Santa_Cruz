<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kk_profiling_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('barangay_id')->constrained('barangays')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->date('date_start');
            $table->date('date_expiry');
            $table->string('link', 300)->nullable();
            $table->enum('status', ['Upcoming', 'Ongoing', 'Completed', 'Cancelled', 'Rescheduled'])->default('Upcoming');
            $table->timestamps();

            $table->index(['barangay_id', 'status']);
            $table->index(['date_start', 'date_expiry']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kk_profiling_schedules');
    }
};
