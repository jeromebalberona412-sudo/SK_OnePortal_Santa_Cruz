<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->string('code', 80)->unique();
                $table->string('municipality')->nullable();
                $table->string('province')->nullable();
                $table->string('region')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        $tenantCode = (string) config('sk_official_auth.tenant_code', 'santa_cruz');

        if ($tenantCode !== '' && Schema::hasTable('tenants')) {
            $exists = DB::table('tenants')->where('code', $tenantCode)->exists();

            if (! $exists) {
                DB::table('tenants')->insert([
                    'name' => 'Santa Cruz Federation',
                    'code' => $tenantCode,
                    'municipality' => 'Santa Cruz',
                    'province' => 'Laguna',
                    'region' => 'IV-A CALABARZON',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (! Schema::hasTable('barangays')) {
            Schema::create('barangays', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
                $table->string('name');
                $table->string('municipality')->default('Santa Cruz');
                $table->string('province')->default('Laguna');
                $table->string('region')->default('IV-A CALABARZON');
                $table->timestamps();

                $table->unique(['tenant_id', 'name'], 'barangays_tenant_name_unique');
            });
        }

        if (! Schema::hasTable('official_profiles')) {
            Schema::create('official_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('middle_name', 100)->nullable();
                $table->string('suffix', 20)->nullable();
                $table->date('date_of_birth')->nullable();
                $table->unsignedTinyInteger('age')->nullable();
                $table->string('contact_number', 20)->nullable();
                $table->string('position');
                $table->string('municipality')->default('Santa Cruz');
                $table->string('province')->default('Laguna');
                $table->string('region')->default('IV-A CALABARZON');
                $table->timestamps();

                $table->index('tenant_id', 'official_profiles_tenant_id_index');
            });
        }

        if (! Schema::hasTable('official_terms')) {
            Schema::create('official_terms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('official_profile_id')->constrained('official_profiles')->cascadeOnDelete();
                $table->date('term_start');
                $table->date('term_end');
                $table->string('status', 30)->default('ACTIVE');
                $table->timestamps();

                $table->index(['official_profile_id', 'term_end']);
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('official_terms');
        Schema::dropIfExists('official_profiles');
        Schema::dropIfExists('barangays');
        Schema::dropIfExists('tenants');
    }
};
