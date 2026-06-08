<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barangay_id')->constrained('barangays')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('note_date');
            $table->string('title', 255);
            $table->string('content', 500);
            $table->timestamps();

            $table->unique(['barangay_id', 'note_date']);
            $table->index(['barangay_id', 'note_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_notes');
    }
};
