<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columns already present on public.abyip (from the original table
     * plus later review/signature migrations) are skipped. This only
     * adds fields the model already expects that are missing from the
     * base schema, plus genuinely new edit/extraction-review fields.
     *
     * @var list<string>
     */
    private const COLUMNS = [
        'category',
        'activity_name',
        'implementation_start',
        'implementation_end',
        'progress_percent',
        'accomplishment_status',
        'target_date',
        'completed_at',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'source_text',
        'page_number',
        'extraction_confidence',
        'extraction_status',
        'manual_review_required',
        'validation_status',
        'validation_message',
        'last_edited_by',
        'last_edited_at',
        'edit_reason',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('abyip')) {
            return;
        }

        Schema::table('abyip', function (Blueprint $table) {
            if (! Schema::hasColumn('abyip', 'category')) {
                $table->string('category', 255)->nullable()->after('code');
            }

            if (! Schema::hasColumn('abyip', 'activity_name')) {
                $table->string('activity_name', 255)->nullable()->after('program_name');
            }

            if (! Schema::hasColumn('abyip', 'implementation_start')) {
                $table->date('implementation_start')->nullable()->after('performance_indicator');
            }

            if (! Schema::hasColumn('abyip', 'implementation_end')) {
                $table->date('implementation_end')->nullable()->after('implementation_start');
            }

            if (! Schema::hasColumn('abyip', 'progress_percent')) {
                $table->decimal('progress_percent', 5, 2)->nullable()->after('sort_order');
            }

            if (! Schema::hasColumn('abyip', 'accomplishment_status')) {
                $table->string('accomplishment_status', 50)->nullable()->after('progress_percent');
            }

            if (! Schema::hasColumn('abyip', 'target_date')) {
                $table->date('target_date')->nullable()->after('accomplishment_status');
            }

            if (! Schema::hasColumn('abyip', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('target_date');
            }

            if (! Schema::hasColumn('abyip', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('completed_at');
            }

            if (! Schema::hasColumn('abyip', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('submitted_at');
            }

            if (! Schema::hasColumn('abyip', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }

            if (! Schema::hasColumn('abyip', 'source_text')) {
                $table->text('source_text')->nullable()->after('rejected_at');
            }

            if (! Schema::hasColumn('abyip', 'page_number')) {
                $table->unsignedSmallInteger('page_number')->nullable()->after('source_text');
            }

            if (! Schema::hasColumn('abyip', 'extraction_confidence')) {
                $table->decimal('extraction_confidence', 5, 2)->nullable()->after('page_number');
            }

            if (! Schema::hasColumn('abyip', 'extraction_status')) {
                $table->string('extraction_status', 30)->nullable()->after('extraction_confidence');
            }

            if (! Schema::hasColumn('abyip', 'manual_review_required')) {
                $table->boolean('manual_review_required')->default(false)->after('extraction_status');
            }

            if (! Schema::hasColumn('abyip', 'validation_status')) {
                $table->string('validation_status', 30)->nullable()->after('manual_review_required');
            }

            if (! Schema::hasColumn('abyip', 'validation_message')) {
                $table->text('validation_message')->nullable()->after('validation_status');
            }

            if (! Schema::hasColumn('abyip', 'last_edited_by')) {
                $table->unsignedBigInteger('last_edited_by')->nullable()->after('validation_message');
            }

            if (! Schema::hasColumn('abyip', 'last_edited_at')) {
                $table->timestamp('last_edited_at')->nullable()->after('last_edited_by');
            }

            if (! Schema::hasColumn('abyip', 'edit_reason')) {
                $table->text('edit_reason')->nullable()->after('last_edited_at');
            }
        });

        if (Schema::hasColumn('abyip', 'last_edited_by') && ! $this->foreignKeyExists('abyip_last_edited_by_foreign')) {
            Schema::table('abyip', function (Blueprint $table) {
                $table->foreign('last_edited_by', 'abyip_last_edited_by_foreign')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('abyip')) {
            return;
        }

        if ($this->foreignKeyExists('abyip_last_edited_by_foreign')) {
            Schema::table('abyip', function (Blueprint $table) {
                $table->dropForeign('abyip_last_edited_by_foreign');
            });
        }

        $existing = array_values(array_filter(
            self::COLUMNS,
            fn (string $column) => Schema::hasColumn('abyip', $column)
        ));

        if ($existing === []) {
            return;
        }

        Schema::table('abyip', function (Blueprint $table) use ($existing) {
            $table->dropColumn($existing);
        });
    }

    private function foreignKeyExists(string $name): bool
    {
        try {
            $rows = Schema::getConnection()->select(
                'select 1 from information_schema.table_constraints where constraint_name = ? and table_name = ?',
                [$name, 'abyip']
            );

            return $rows !== [];
        } catch (Throwable) {
            return false;
        }
    }
};
