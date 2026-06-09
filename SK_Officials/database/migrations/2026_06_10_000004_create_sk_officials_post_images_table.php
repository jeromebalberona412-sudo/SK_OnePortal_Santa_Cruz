<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('announcement_images')) {
            Schema::create('announcement_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('announcement_id')->constrained('announcements')->cascadeOnDelete();
                $table->text('image_url');
                $table->string('public_id', 255)->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamp('created_at')->nullable();

                $table->index(['announcement_id', 'sort_order']);
            });
        }

        if (Schema::hasTable('announcements') && Schema::hasTable('announcement_images')) {
            DB::table('announcements')
                ->whereNotNull('image_url')
                ->where('image_url', '!=', '')
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    $now = now();
                    $inserts = [];

                    foreach ($rows as $row) {
                        $exists = DB::table('announcement_images')
                            ->where('announcement_id', $row->id)
                            ->exists();

                        if ($exists) {
                            continue;
                        }

                        $inserts[] = [
                            'announcement_id' => $row->id,
                            'image_url' => $row->image_url,
                            'public_id' => null,
                            'sort_order' => 0,
                            'created_at' => $now,
                        ];
                    }

                    if ($inserts !== []) {
                        DB::table('announcement_images')->insert($inserts);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_images');
    }
};
