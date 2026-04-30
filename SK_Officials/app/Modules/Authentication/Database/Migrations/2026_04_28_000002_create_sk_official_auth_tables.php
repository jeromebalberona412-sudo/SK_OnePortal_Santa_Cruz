<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sk_official_login_attempts')) {
            Schema::create('sk_official_login_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('email')->index();
                $table->string('ip_address', 45)->index();
                $table->boolean('successful')->default(false);
                $table->text('user_agent')->nullable();
                $table->timestamp('attempted_at')->useCurrent();
                $table->json('metadata')->nullable();

                $table->index(['email', 'successful', 'attempted_at'], 'sk_official_login_attempt_email_idx');
                $table->index(['ip_address', 'successful', 'attempted_at'], 'sk_official_login_attempt_ip_idx');
            });
        }

        if (! Schema::hasTable('sk_official_auth_audit_logs')) {
            Schema::create('sk_official_auth_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
                $table->string('actor_email', 255)->nullable();
                $table->string('event', 120);
                $table->string('outcome', 20)->nullable();
                $table->string('resource_type', 120)->nullable();
                $table->string('resource_id', 120)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['event', 'created_at'], 'sk_official_auth_audit_event_idx');
                $table->index(['user_id', 'created_at'], 'sk_official_auth_audit_user_idx');
                $table->index(['tenant_id', 'created_at'], 'sk_official_auth_audit_tenant_idx');
                $table->index(['outcome', 'created_at'], 'sk_official_auth_audit_outcome_idx');
                $table->index(['resource_type', 'resource_id'], 'sk_official_auth_resource_idx');
            });
        }

        if (! Schema::hasTable('sk_official_trusted_devices')) {
            Schema::create('sk_official_trusted_devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('fingerprint', 128);
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'fingerprint'], 'sk_official_trusted_device_unique');
                $table->index(['user_id', 'expires_at'], 'sk_official_trusted_device_exp_idx');
            });
        }

        if (! Schema::hasTable('sk_official_email_verified_devices')) {
            Schema::create('sk_official_email_verified_devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->string('fingerprint', 128);
                $table->timestamp('verified_at')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index('verified_at', 'sk_official_verified_device_verified_at_idx');
            });
        }

        if (! Schema::hasTable('sk_official_feature_flags')) {
            Schema::create('sk_official_feature_flags', function (Blueprint $table) {
                $table->id();
                $table->string('flag_key', 190)->unique();
                $table->boolean('enabled')->default(false);
                $table->string('description')->nullable();
                $table->unsignedTinyInteger('rollout_percentage')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });

            DB::table('sk_official_feature_flags')->insert([
                [
                    'flag_key' => 'features.device_verification',
                    'enabled' => true,
                    'description' => 'Enable trusted-device checks for SK Officials.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'flag_key' => 'features.login_alert_notifications',
                    'enabled' => true,
                    'description' => 'Send login alerts for unusual SK Official sign-ins.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'flag_key' => 'features.suspicious_login_detection',
                    'enabled' => true,
                    'description' => 'Detect suspicious SK Official login patterns.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sk_official_feature_flags');
        Schema::dropIfExists('sk_official_email_verified_devices');
        Schema::dropIfExists('sk_official_trusted_devices');
        Schema::dropIfExists('sk_official_auth_audit_logs');
        Schema::dropIfExists('sk_official_login_attempts');
    }
};
