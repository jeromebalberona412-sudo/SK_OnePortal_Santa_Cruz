<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarship_school_years', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->foreignId('barangay_id')->constrained()->cascadeOnDelete();
            $table->string('label', 9);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['barangay_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_school_years');
    }
};
