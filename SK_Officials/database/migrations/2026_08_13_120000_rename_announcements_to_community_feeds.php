<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->renameTableIfNeeded('announcements', 'community_feeds');
        $this->renameTableIfNeeded('announcement_comments', 'community_feed_comments');
        $this->renameTableIfNeeded('announcement_reactions', 'community_feed_reactions');
        $this->renameTableIfNeeded('announcement_images', 'community_feed_images');

        $this->renameForeignIdColumn('community_feed_comments', 'announcement_id', 'community_feed_id', 'community_feeds');
        $this->renameForeignIdColumn('community_feed_reactions', 'announcement_id', 'community_feed_id', 'community_feeds');
        $this->renameForeignIdColumn('community_feed_images', 'announcement_id', 'community_feed_id', 'community_feeds');

        $this->ensureReactionTypeColumn();
        $this->ensureCommentParentForeignKey();
        $this->ensureIndexes();
    }

    public function down(): void
    {
        $this->renameForeignIdColumn('community_feed_comments', 'community_feed_id', 'announcement_id', 'community_feeds');
        $this->renameForeignIdColumn('community_feed_reactions', 'community_feed_id', 'announcement_id', 'community_feeds');
        $this->renameForeignIdColumn('community_feed_images', 'community_feed_id', 'announcement_id', 'community_feeds');

        $this->renameTableIfNeeded('community_feed_images', 'announcement_images');
        $this->renameTableIfNeeded('community_feed_reactions', 'announcement_reactions');
        $this->renameTableIfNeeded('community_feed_comments', 'announcement_comments');
        $this->renameTableIfNeeded('community_feeds', 'announcements');
    }

    private function renameTableIfNeeded(string $from, string $to): void
    {
        if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
            Schema::rename($from, $to);
        }
    }

    private function renameForeignIdColumn(string $table, string $from, string $to, string $referencedTable): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $from) || Schema::hasColumn($table, $to)) {
            return;
        }

        $this->dropForeignKeysOnColumn($table, $from);

        Schema::table($table, function (Blueprint $blueprint) use ($from, $to) {
            $blueprint->renameColumn($from, $to);
        });

        Schema::table($table, function (Blueprint $blueprint) use ($to, $referencedTable) {
            $blueprint->foreign($to)->references('id')->on($referencedTable)->cascadeOnDelete();
        });
    }

    private function dropForeignKeysOnColumn(string $table, string $column): void
    {
        $constraints = DB::select(
            "SELECT tc.constraint_name
             FROM information_schema.table_constraints tc
             JOIN information_schema.key_column_usage kcu
               ON tc.constraint_name = kcu.constraint_name
              AND tc.table_schema = kcu.table_schema
             WHERE tc.table_schema = current_schema()
               AND tc.table_name = ?
               AND tc.constraint_type = 'FOREIGN KEY'
               AND kcu.column_name = ?",
            [$table, $column]
        );

        foreach ($constraints as $constraint) {
            DB::statement(sprintf(
                'ALTER TABLE %s DROP CONSTRAINT %s',
                $this->quoteIdent($table),
                $this->quoteIdent($constraint->constraint_name)
            ));
        }
    }

    private function ensureReactionTypeColumn(): void
    {
        if (! Schema::hasTable('community_feed_reactions')) {
            return;
        }

        if (! Schema::hasColumn('community_feed_reactions', 'reaction_type')) {
            Schema::table('community_feed_reactions', function (Blueprint $table) {
                $table->string('reaction_type', 30)->default('like')->after('user_type');
            });
        }

        DB::table('community_feed_reactions')
            ->where(function ($query) {
                $query->whereNull('reaction_type')->orWhere('reaction_type', '');
            })
            ->update(['reaction_type' => 'like']);
    }

    private function ensureCommentParentForeignKey(): void
    {
        if (! Schema::hasTable('community_feed_comments')) {
            return;
        }

        if (! Schema::hasColumn('community_feed_comments', 'parent_id')) {
            Schema::table('community_feed_comments', function (Blueprint $table) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('community_feed_id');
            });
        }

        $exists = DB::selectOne(
            "SELECT 1
             FROM information_schema.table_constraints
             WHERE table_schema = current_schema()
               AND table_name = 'community_feed_comments'
               AND constraint_name = 'community_feed_comments_parent_id_foreign'"
        );

        if (! $exists) {
            Schema::table('community_feed_comments', function (Blueprint $table) {
                $table->foreign('parent_id', 'community_feed_comments_parent_id_foreign')
                    ->references('id')
                    ->on('community_feed_comments')
                    ->cascadeOnDelete();
            });
        }
    }

    private function ensureIndexes(): void
    {
        $this->createIndexIfMissing(
            'community_feed_comments',
            'community_feed_comments_parent_id_index',
            ['parent_id']
        );
        $this->createIndexIfMissing(
            'community_feed_comments',
            'community_feed_comments_feed_created_at_index',
            ['community_feed_id', 'created_at']
        );
        $this->createIndexIfMissing(
            'community_feed_reactions',
            'community_feed_reactions_feed_index',
            ['community_feed_id']
        );
        $this->createIndexIfMissing(
            'community_feed_images',
            'community_feed_images_feed_sort_order_index',
            ['community_feed_id', 'sort_order']
        );
    }

    /**
     * @param  list<string>  $columns
     */
    private function createIndexIfMissing(string $table, string $index, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $exists = DB::selectOne(
            'SELECT 1 FROM pg_indexes WHERE schemaname = current_schema() AND indexname = ?',
            [$index]
        );

        if ($exists) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $index) {
            $blueprint->index($columns, $index);
        });
    }

    private function quoteIdent(string $name): string
    {
        return '"'.str_replace('"', '""', $name).'"';
    }
};
