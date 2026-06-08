<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('abyips', 'sk_youth_development_and_empowerment_programs')) {
            Schema::table('abyips', function (Blueprint $table) {
                $table->dropColumn('sk_youth_development_and_empowerment_programs');
            });
        }

        Schema::dropIfExists('abyip_detected_programs');
        Schema::dropIfExists('abyip_program_activities');
        Schema::dropIfExists('abyip_programs');

        Schema::create('abyip_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abyip_id')->constrained('abyips')->cascadeOnDelete();
            $table->char('program_letter', 1)->nullable();
            $table->string('program_name');
            $table->unsignedInteger('sort_order')->nullable();
            $table->timestamps();
        });

        Schema::create('abyip_program_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abyip_id')->constrained('abyips')->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('abyip_programs')->cascadeOnDelete();
            $table->string('activity_name')->nullable();
            $table->string('code', 50)->nullable();
            $table->string('ppas')->nullable();
            $table->text('description')->nullable();
            $table->text('expected_result')->nullable();
            $table->text('performance_indicator')->nullable();
            $table->string('period_of_implementation')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->string('person_responsible')->nullable();
            $table->decimal('mooe', 15, 2)->nullable();
            $table->decimal('co', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->string('row_type', 30)->nullable();
            $table->string('program_section')->nullable();
            $table->unsignedInteger('sort_order')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abyip_program_activities');
        Schema::dropIfExists('abyip_programs');

        Schema::create('abyip_programs', function (Blueprint $table) {
            $table->char('id', 1)->primary();
            $table->string('programs');
            $table->timestamps();
        });

        Schema::create('abyip_detected_programs', function (Blueprint $table) {
            $table->foreignId('abyip_id')->constrained('abyips')->cascadeOnDelete();
            $table->char('program_id', 1);
            $table->primary(['abyip_id', 'program_id']);
            $table->foreign('program_id')->references('id')->on('abyip_programs')->cascadeOnDelete();
        });

        if (! Schema::hasColumn('abyips', 'sk_youth_development_and_empowerment_programs')) {
            Schema::table('abyips', function (Blueprint $table) {
                $table->jsonb('sk_youth_development_and_empowerment_programs')->nullable();
            });
        }
    }
};
