<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('barangay_id')->constrained('barangays')->onDelete('cascade');
            $table->enum('type', ['announcement', 'event', 'activity', 'program', 'update'])->default('announcement');
            $table->string('title')->nullable();
            $table->text('body');
            $table->string('image_url')->nullable();
            $table->string('link_url')->nullable();
            $table->timestamps();

            $table->index(['barangay_id', 'created_at']);
        });

        Schema::create('announcement_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->string('user_type', 20); // 'sk_official' | 'kabataan'
            $table->timestamps();

            $table->unique(['announcement_id', 'user_id', 'user_type']);
        });

        Schema::create('announcement_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->string('user_type', 20); // 'sk_official' | 'kabataan'
            $table->string('author_name');
            $table->text('body');
            $table->timestamps();

            $table->index(['announcement_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_comments');
        Schema::dropIfExists('announcement_reactions');
        Schema::dropIfExists('announcements');
    }
};
