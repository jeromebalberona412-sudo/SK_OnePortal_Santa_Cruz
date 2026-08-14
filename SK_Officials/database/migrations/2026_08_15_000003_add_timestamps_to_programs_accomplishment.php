<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('programs_accomplishment')) {
            return;
        }

        Schema::table('programs_accomplishment', function (Blueprint $table) {
            if (! Schema::hasColumn('programs_accomplishment', 'image_url')) {
                $table->text('image_url')->nullable();
            }
            if (! Schema::hasColumn('programs_accomplishment', 'secure_url')) {
                $table->text('secure_url')->nullable();
            }
            if (! Schema::hasColumn('programs_accomplishment', 'cloudinary_public_id')) {
                $table->string('cloudinary_public_id')->nullable();
            }
            if (! Schema::hasColumn('programs_accomplishment', 'display_name')) {
                $table->string('display_name')->nullable();
            }
            if (! Schema::hasColumn('programs_accomplishment', 'caption')) {
                $table->string('caption')->nullable();
            }
            if (! Schema::hasColumn('programs_accomplishment', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(1);
            }
            if (! Schema::hasColumn('programs_accomplishment', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (! Schema::hasColumn('programs_accomplishment', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Keep image URL columns; they are required for Cloudinary storage.
    }
};
