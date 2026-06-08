<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sk_official_trusted_devices')) {
            return;
        }

        Schema::create('sk_official_trusted_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('fingerprint', 64);
            $table->string('user_agent', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->unique(['user_id', 'fingerprint']);
            $table->index(['user_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sk_official_trusted_devices');
    }
};
