<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('program_surveys')) {
            Schema::create('program_surveys', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('barangay_id')->constrained('barangays')->cascadeOnDelete();
                $table->unsignedBigInteger('abyip_id');
                $table->unsignedBigInteger('abyip_program_id');
                $table->text('announcement');
                $table->text('instructions');
                $table->date('open_date');
                $table->date('close_date');
                $table->string('status', 30);
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->foreign('abyip_id')->references('id')->on('abyip')->cascadeOnDelete();
                $table->foreign('abyip_program_id')->references('id')->on('abyip')->cascadeOnDelete();

                $table->index(['barangay_id', 'status']);
                $table->index(['barangay_id', 'abyip_program_id']);
                $table->index(['barangay_id', 'open_date', 'close_date']);
            });

            DB::statement(
                'CREATE UNIQUE INDEX program_surveys_barangay_program_year_unique '
                .'ON program_surveys (barangay_id, abyip_program_id, (EXTRACT(YEAR FROM open_date)))'
            );
        }

        if (! Schema::hasTable('program_survey_questions')) {
            Schema::create('program_survey_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('survey_id')->constrained('program_surveys')->cascadeOnDelete();
                $table->text('question_label');
                $table->string('input_type', 50);
                $table->boolean('is_required')->default(true);
                $table->json('options')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index(['survey_id', 'sort_order']);
            });
        }

        if (! Schema::hasTable('program_survey_responses')) {
            Schema::create('program_survey_responses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('survey_id')->constrained('program_surveys')->cascadeOnDelete();
                $table->unsignedBigInteger('registration_id');
                $table->timestamp('submitted_at')->useCurrent();
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('registration_id')->references('id')->on('kabataan_registrations')->cascadeOnDelete();
                $table->index(['survey_id', 'submitted_at']);
            });
        }

        if (! Schema::hasTable('program_survey_response_answers')) {
            Schema::create('program_survey_response_answers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('response_id')->constrained('program_survey_responses')->cascadeOnDelete();
                $table->foreignId('question_id')->constrained('program_survey_questions')->cascadeOnDelete();
                $table->text('answer')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['response_id', 'question_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('program_survey_response_answers');
        Schema::dropIfExists('program_survey_responses');
        Schema::dropIfExists('program_survey_questions');

        if (Schema::hasTable('program_surveys')) {
            DB::statement('DROP INDEX IF EXISTS program_surveys_barangay_program_year_unique');
            Schema::dropIfExists('program_surveys');
        }
    }
};
