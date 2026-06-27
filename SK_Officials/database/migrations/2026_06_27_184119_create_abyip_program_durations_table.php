<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abyip_program_durations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barangay_id')->constrained('barangays')->cascadeOnDelete();
            $table->unsignedBigInteger('abyip_program_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->foreign('abyip_program_id')->references('id')->on('abyip')->cascadeOnDelete();
            $table->unique(['barangay_id', 'abyip_program_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abyip_program_durations');
    }
};
