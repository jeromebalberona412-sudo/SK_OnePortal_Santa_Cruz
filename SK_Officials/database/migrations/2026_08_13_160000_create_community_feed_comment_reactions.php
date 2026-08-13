<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('community_feed_comment_reactions')) {
            return;
        }

        Schema::create('community_feed_comment_reactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comment_id');
            $table->unsignedBigInteger('user_id');
            $table->string('user_type', 20);
            $table->string('reaction_type', 30)->default('like');
            $table->timestamps();

            $table->unique(['comment_id', 'user_id', 'user_type'], 'cf_comment_reactions_unique');
            $table->index(['comment_id', 'reaction_type'], 'cf_comment_reactions_type_index');
        });

        if (Schema::hasTable('community_feed_comments')) {
            Schema::table('community_feed_comment_reactions', function (Blueprint $table) {
                $table->foreign('comment_id', 'cf_comment_reactions_comment_fk')
                    ->references('id')
                    ->on('community_feed_comments')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('community_feed_comment_reactions');
    }
};
