<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sk_fed_device_verification_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token_hash', 128);
            $table->string('fingerprint', 128);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'expires_at'], 'sk_fed_device_token_exp_idx');
            $table->index(['token_hash', 'used_at'], 'sk_fed_device_token_hash_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sk_fed_device_verification_tokens');
    }
};
