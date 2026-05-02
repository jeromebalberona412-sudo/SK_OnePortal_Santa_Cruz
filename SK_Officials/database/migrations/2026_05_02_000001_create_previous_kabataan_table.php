<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('previous_kabataan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kabataan_registration_id')->nullable()->constrained('kabataan_registrations')->onDelete('set null');
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('barangay_id')->constrained('barangays')->onDelete('cascade');
            $table->foreignId('moved_by_user_id')->nullable()->constrained('users')->onDelete('set null');

            // Profile
            $table->string('last_name', 100);
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('suffix', 10)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('contact_number', 15)->nullable();

            // Full form snapshot
            $table->json('form_data');

            // Archive metadata
            $table->year('profiling_year');
            $table->timestamp('moved_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'barangay_id']);
            $table->index('profiling_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('previous_kabataan');
    }
};
