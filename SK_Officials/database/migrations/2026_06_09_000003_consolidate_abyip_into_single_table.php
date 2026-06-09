<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('abyip')) {
            return;
        }

        Schema::create('abyip', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('barangay_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedSmallInteger('fiscal_year');

            $table->string('country', 100)->default('Republic of the Philippines');
            $table->string('region', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('municipality', 100)->nullable();
            $table->string('barangay_name', 255)->nullable();
            $table->string('document_title', 255)->default('');
            $table->string('sk_council_name', 255)->nullable();
            $table->decimal('barangay_estimated_budget', 15, 2)->default(0);
            $table->decimal('sk_fund_percentage', 5, 2)->default(10);
            $table->decimal('sk_fund_amount', 15, 2)->default(0);
            $table->decimal('total_budget', 15, 2)->nullable();
            $table->string('prepared_by', 255)->nullable();
            $table->string('prepared_position', 255)->nullable();
            $table->unsignedBigInteger('prepared_by_user_id')->nullable();
            $table->string('approved_by', 255)->nullable();
            $table->string('approved_position', 255)->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->string('source_type', 20)->default('word');
            $table->text('document_html')->nullable();
            $table->text('pdf_data')->nullable();

            $table->string('row_type', 30)->default('document');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('code', 20)->nullable();
            $table->string('program_name', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('expected_result')->nullable();
            $table->text('performance_indicator')->nullable();
            $table->string('implementation_period', 255)->nullable();
            $table->string('person_responsible', 255)->nullable();
            $table->decimal('mooe', 15, 2)->nullable();
            $table->decimal('co', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->integer('sort_order')->nullable();

            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            $table->foreign('barangay_id')->references('id')->on('barangays')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('prepared_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['barangay_id', 'fiscal_year']);
            $table->index('document_id');
            $table->index(['document_id', 'row_type']);
            $table->index('parent_id');
        });

        Schema::table('abyip', function (Blueprint $table) {
            $table->foreign('document_id')->references('id')->on('abyip')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('abyip')->cascadeOnDelete();
        });

        DB::statement("CREATE UNIQUE INDEX abyip_barangay_fiscal_year_document_idx ON abyip (barangay_id, fiscal_year) WHERE row_type = 'document'");

        $this->migrateLegacyData();

        Schema::dropIfExists('abyip_program_activities');
        Schema::dropIfExists('abyip_programs');
        Schema::dropIfExists('abyips');
        Schema::dropIfExists('abyip_detected_programs');
    }

    public function down(): void
    {
        if (! Schema::hasTable('abyip')) {
            return;
        }

        Schema::create('abyips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('barangay_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedSmallInteger('fiscal_year');
            $table->string('country', 100)->default('Republic of the Philippines');
            $table->string('region', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('municipality', 100)->nullable();
            $table->string('barangay_name', 255)->nullable();
            $table->string('document_title', 255);
            $table->string('sk_council_name', 255)->nullable();
            $table->decimal('barangay_estimated_budget', 15, 2)->default(0);
            $table->decimal('sk_fund_percentage', 5, 2)->default(10);
            $table->decimal('sk_fund_amount', 15, 2)->default(0);
            $table->decimal('total_budget', 15, 2)->nullable();
            $table->string('prepared_by', 255)->nullable();
            $table->string('prepared_position', 255)->nullable();
            $table->unsignedBigInteger('prepared_by_user_id')->nullable();
            $table->string('approved_by', 255)->nullable();
            $table->string('approved_position', 255)->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->string('source_type', 20)->default('word');
            $table->text('document_html')->nullable();
            $table->text('pdf_data')->nullable();
            $table->timestamps();
            $table->unique(['barangay_id', 'fiscal_year']);
        });

        Schema::create('abyip_programs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('abyip_id');
            $table->string('code', 20)->nullable();
            $table->string('program_name', 255);
            $table->text('description')->nullable();
            $table->text('expected_result')->nullable();
            $table->text('performance_indicator')->nullable();
            $table->string('implementation_period', 255)->nullable();
            $table->string('person_responsible', 255)->nullable();
            $table->string('row_type', 30)->nullable();
            $table->integer('sort_order')->nullable();
            $table->timestamps();
            $table->foreign('abyip_id')->references('id')->on('abyips')->cascadeOnDelete();
        });

        Schema::create('abyip_program_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('abyip_id');
            $table->unsignedBigInteger('program_id');
            $table->string('activity_name', 255);
            $table->decimal('budget', 15, 2)->nullable();
            $table->decimal('mooe', 15, 2)->nullable();
            $table->decimal('co', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->integer('sort_order')->nullable();
            $table->timestamps();
            $table->foreign('abyip_id')->references('id')->on('abyips')->cascadeOnDelete();
            $table->foreign('program_id')->references('id')->on('abyip_programs')->cascadeOnDelete();
        });

        Schema::dropIfExists('abyip');
    }

    private function migrateLegacyData(): void
    {
        if (! Schema::hasTable('abyips')) {
            return;
        }

        $documents = DB::table('abyips')->orderBy('id')->get();

        foreach ($documents as $document) {
            $documentId = DB::table('abyip')->insertGetId([
                'document_id' => null,
                'tenant_id' => $document->tenant_id,
                'barangay_id' => $document->barangay_id,
                'created_by' => $document->created_by,
                'fiscal_year' => $document->fiscal_year,
                'country' => $document->country ?? 'Republic of the Philippines',
                'region' => $document->region,
                'province' => $document->province,
                'municipality' => $document->municipality,
                'barangay_name' => $document->barangay_name,
                'document_title' => $document->document_title,
                'sk_council_name' => $document->sk_council_name,
                'barangay_estimated_budget' => $document->barangay_estimated_budget,
                'sk_fund_percentage' => $document->sk_fund_percentage,
                'sk_fund_amount' => $document->sk_fund_amount,
                'total_budget' => $document->total_budget,
                'prepared_by' => $document->prepared_by,
                'prepared_position' => $document->prepared_position,
                'prepared_by_user_id' => $document->prepared_by_user_id,
                'approved_by' => $document->approved_by,
                'approved_position' => $document->approved_position,
                'approved_by_user_id' => $document->approved_by_user_id,
                'source_type' => $document->source_type ?? 'word',
                'document_html' => $document->document_html,
                'pdf_data' => $document->pdf_data,
                'row_type' => 'document',
                'created_at' => $document->created_at,
                'updated_at' => $document->updated_at,
            ]);

            DB::table('abyip')->where('id', $documentId)->update(['document_id' => $documentId]);

            if (! Schema::hasTable('abyip_programs')) {
                continue;
            }

            $programIdMap = [];
            $programs = DB::table('abyip_programs')
                ->where('abyip_id', $document->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ($programs as $program) {
                $newProgramId = DB::table('abyip')->insertGetId([
                    'document_id' => $documentId,
                    'tenant_id' => $document->tenant_id,
                    'barangay_id' => $document->barangay_id,
                    'created_by' => $document->created_by,
                    'fiscal_year' => $document->fiscal_year,
                    'row_type' => $program->row_type ?? 'expenditure',
                    'code' => $program->code,
                    'program_name' => $program->program_name,
                    'description' => $program->description,
                    'expected_result' => $program->expected_result,
                    'performance_indicator' => $program->performance_indicator,
                    'implementation_period' => $program->implementation_period,
                    'person_responsible' => $program->person_responsible,
                    'sort_order' => $program->sort_order,
                    'created_at' => $program->created_at,
                    'updated_at' => $program->updated_at,
                ]);

                $programIdMap[$program->id] = $newProgramId;
            }

            if (! Schema::hasTable('abyip_program_activities')) {
                continue;
            }

            $activities = DB::table('abyip_program_activities')
                ->where('abyip_id', $document->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ($activities as $activity) {
                DB::table('abyip')->insert([
                    'document_id' => $documentId,
                    'tenant_id' => $document->tenant_id,
                    'barangay_id' => $document->barangay_id,
                    'created_by' => $document->created_by,
                    'fiscal_year' => $document->fiscal_year,
                    'row_type' => 'activity',
                    'parent_id' => $programIdMap[$activity->program_id] ?? null,
                    'program_name' => $activity->activity_name,
                    'budget' => $activity->budget,
                    'mooe' => $activity->mooe,
                    'co' => $activity->co,
                    'total' => $activity->total,
                    'sort_order' => $activity->sort_order,
                    'created_at' => $activity->created_at,
                    'updated_at' => $activity->updated_at,
                ]);
            }
        }
    }
};
