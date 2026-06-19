<?php

use App\Modules\Accounts\Models\Barangay;
use App\Modules\Accounts\Models\OfficialProfile;
use App\Modules\Accounts\Models\OfficialTerm;
use App\Modules\Accounts\Services\BatchAccountImportService;
use App\Modules\Accounts\Services\ChairpersonFederationSyncService;
use App\Modules\Shared\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('validates batch rows using simplified officials template rules', function () {
    $tenantId = (int) DB::table('tenants')->where('code', config('sk_fed_auth.tenant_code'))->value('id');
    expect($tenantId)->toBeGreaterThan(0);

    $service = new BatchAccountImportService($tenantId);

    $errors = $service->validateRows([
        [
            'first_name' => 'Jo',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
            'barangay' => 'San Roque',
            'position' => 'Chairperson',
            'sex' => 'Male',
            'date_of_birth' => '2000-01-01',
            'contact_number' => '09171234567',
            'term_start' => now()->startOfYear()->toDateString(),
            'term_end' => '2029-06-30',
        ],
    ], User::ROLE_SK_OFFICIAL);

    expect($errors)->not->toBeEmpty();
});

it('normalizes valid officials batch rows with strict template validation', function () {
    $tenantId = (int) DB::table('tenants')->where('code', config('sk_fed_auth.tenant_code'))->value('id');
    $service = new BatchAccountImportService($tenantId);

    $row = $service->normalizeAccountRow([
        'first_name' => 'JUAN',
        'middle_name' => '',
        'last_name' => 'DELA CRUZ',
        'suffix' => 'Jr.',
        'sex' => 'Male',
        'date_of_birth' => '2000-01-15',
        'contact_number' => '09171234567',
        'email' => 'juan.batch.'.uniqid().'@email.com',
        'barangay' => 'San Roque',
        'position' => 'Chairperson',
        'term_start' => now()->startOfYear()->toDateString(),
        'term_end' => '2029-06-30',
    ], User::ROLE_SK_OFFICIAL, true);

    expect($row['email'])->toContain('@email.com')
        ->and($row['position'])->toBe('Chairperson')
        ->and($row['middle_name'])->toBeNull()
        ->and($row['status'])->toBe(User::STATUS_ACTIVE);
});

it('syncs federation access for chairperson officials only', function () {
    if (! Schema::hasColumn('users', 'has_federation_access')) {
        $this->markTestSkipped('has_federation_access column is not migrated yet.');
    }

    $tenantId = (int) DB::table('tenants')->where('code', config('sk_fed_auth.tenant_code'))->value('id');
    $barangayId = (int) Barangay::query()->where('tenant_id', $tenantId)->value('id');

    $user = User::factory()->create([
        'tenant_id' => $tenantId,
        'barangay_id' => $barangayId ?: null,
        'role' => User::ROLE_SK_OFFICIAL,
        'has_federation_access' => false,
    ]);

    $profile = OfficialProfile::query()->create([
        'tenant_id' => $tenantId,
        'user_id' => $user->id,
        'first_name' => 'JUAN',
        'last_name' => 'DELA CRUZ',
        'position' => 'Chairperson',
        'municipality' => 'Santa Cruz',
        'province' => 'Laguna',
        'region' => 'IV-A CALABARZON',
    ]);

    OfficialTerm::query()->create([
        'official_profile_id' => $profile->id,
        'term_start' => now()->startOfYear()->toDateString(),
        'term_end' => now()->addYears(3)->toDateString(),
        'status' => OfficialTerm::STATUS_ACTIVE,
    ]);

    $sync = new ChairpersonFederationSyncService;
    $sync->syncForUser($user->fresh(), 'Chairperson');

    expect($user->fresh()->has_federation_access)->toBeTrue();

    $user->forceDelete();
    $profile->delete();
});

it('rejects duplicate emails during batch validation', function () {
    $tenantId = (int) DB::table('tenants')->where('code', config('sk_fed_auth.tenant_code'))->value('id');
    $existing = User::query()->where('tenant_id', $tenantId)->first();
    expect($existing)->not->toBeNull();

    $service = new BatchAccountImportService($tenantId);
    $errors = $service->validateRows([
        [
            'first_name' => 'MARIA',
            'last_name' => 'REYES',
            'email' => $existing->email,
            'barangay' => 'San Roque',
            'position' => 'Secretary',
            'sex' => 'Female',
            'date_of_birth' => '2001-05-10',
            'contact_number' => '09179876543',
            'term_start' => now()->startOfYear()->toDateString(),
            'term_end' => '2029-06-30',
        ],
    ], User::ROLE_SK_OFFICIAL);

    expect(collect($errors)->pluck('error')->first())->toContain('already registered');
});
