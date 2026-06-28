<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarship_quick_guideline_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_program_id')
                ->constrained('schedule_programs')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('step_order');
            $table->text('content_en');
            $table->text('content_tl');
            $table->timestamps();

            $table->unique(['schedule_program_id', 'step_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_quick_guideline_steps');
    }
};
