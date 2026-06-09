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

        if (Schema::hasTable('sk_officials_post_images')) {
            DB::table('sk_officials_post_images')
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    $inserts = [];

                    foreach ($rows as $row) {
                        $exists = DB::table('announcement_images')
                            ->where('announcement_id', $row->announcement_id)
                            ->where('image_url', $row->imageurl)
                            ->exists();

                        if ($exists) {
                            continue;
                        }

                        $inserts[] = [
                            'announcement_id' => $row->announcement_id,
                            'image_url' => $row->imageurl,
                            'public_id' => $row->cloudinary_public_id ?? null,
                            'sort_order' => $row->sort_order ?? 0,
                            'created_at' => $row->created_at ?? now(),
                        ];
                    }

                    if ($inserts !== []) {
                        DB::table('announcement_images')->insert($inserts);
                    }
                });

            Schema::dropIfExists('sk_officials_post_images');
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

        if (Schema::hasTable('announcements') && Schema::hasColumn('announcements', 'image_url')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->dropColumn('image_url');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('announcements') && ! Schema::hasColumn('announcements', 'image_url')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->string('image_url')->nullable()->after('body');
            });

            if (Schema::hasTable('announcement_images')) {
                DB::table('announcement_images')
                    ->select('announcement_id', DB::raw('MIN(image_url) as image_url'))
                    ->groupBy('announcement_id')
                    ->orderBy('announcement_id')
                    ->chunk(200, function ($rows) {
                        foreach ($rows as $row) {
                            DB::table('announcements')
                                ->where('id', $row->announcement_id)
                                ->update(['image_url' => $row->image_url]);
                        }
                    });
            }
        }

        if (! Schema::hasTable('sk_officials_post_images') && Schema::hasTable('announcement_images')) {
            Schema::create('sk_officials_post_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('announcement_id')->constrained('announcements')->cascadeOnDelete();
                $table->string('imageurl', 2048);
                $table->string('cloudinary_public_id', 255)->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['announcement_id', 'sort_order']);
            });

            DB::table('announcement_images')
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    $now = now();
                    $inserts = [];

                    foreach ($rows as $row) {
                        $inserts[] = [
                            'announcement_id' => $row->announcement_id,
                            'imageurl' => $row->image_url,
                            'cloudinary_public_id' => $row->public_id,
                            'sort_order' => $row->sort_order ?? 0,
                            'created_at' => $row->created_at ?? $now,
                            'updated_at' => $now,
                        ];
                    }

                    if ($inserts !== []) {
                        DB::table('sk_officials_post_images')->insert($inserts);
                    }
                });
        }
    }
};
