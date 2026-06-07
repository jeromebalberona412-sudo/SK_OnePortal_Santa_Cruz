<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('committees')) {
            return;
        }

        Schema::create('committees', function (Blueprint $table) {
            $table->id();
            $table->string('committee_name');
            $table->foreignId('committee_head_id')->constrained('users')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique('committee_head_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committees');
    }
};
