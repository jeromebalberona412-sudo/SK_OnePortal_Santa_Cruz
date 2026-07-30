<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('program_accomplishment_photos');
        Schema::dropIfExists('program_accomplishment_files');
    }

    public function down(): void
    {
        // recreate photos table (previous migration)
        Schema::create('program_accomplishment_photos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('report_id');
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreign('report_id')->references('id')->on('programs_accomplishment_reports')->onDelete('cascade');
        });

        Schema::create('program_accomplishment_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('report_id');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type')->nullable();
            $table->foreign('report_id')->references('id')->on('programs_accomplishment_reports')->onDelete('cascade');
        });
    }
};
