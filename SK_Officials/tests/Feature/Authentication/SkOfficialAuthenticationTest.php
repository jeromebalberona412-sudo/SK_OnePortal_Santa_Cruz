<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

if (! function_exists('skOfficialTenantId')) {
    function skOfficialTenantId(): int
    {
        return (int) DB::table('tenants')->where('code', config('sk_official_auth.tenant_code'))->value('id');
    }
}

if (! function_exists('skOfficialBarangayId')) {
    function skOfficialBarangayId(): int
    {
        $tenantId = skOfficialTenantId();

        return (int) DB::table('barangays')->insertGetId([
            'tenant_id' => $tenantId,
            'name' => fake()->unique()->city(),
            'municipality' => 'Santa Cruz',
            'province' => 'Laguna',
            'region' => 'IV-A CALABARZON',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

it('allows an active verified sk official to login', function () {
    $user = User::factory()->create([
        'email' => 'official@example.com',
        'password' => 'Password123!',
        'tenant_id' => skOfficialTenantId(),
        'barangay_id' => skOfficialBarangayId(),
        'role' => User::ROLE_SK_OFFICIAL,
        'status' => User::STATUS_ACTIVE,
        'email_verified_at' => now(),
        'must_change_password' => false,
    ]);

    post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ])->assertRedirect('/dashboard');

    assertAuthenticatedAs($user);
});

it('blocks non official roles from the sk officials portal', function (string $role) {
    $user = User::factory()->create([
        'email' => $role.'@example.com',
        'password' => 'Password123!',
        'tenant_id' => skOfficialTenantId(),
        'barangay_id' => skOfficialBarangayId(),
        'role' => $role,
        'status' => User::STATUS_ACTIVE,
        'email_verified_at' => now(),
    ]);

    from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ])->assertRedirect('/login');

    assertGuest();
})->with([
    User::ROLE_ADMIN,
    User::ROLE_SK_FED,
    User::ROLE_USER,
]);

it('blocks inactive officials and officials without barangay ownership', function (array $overrides) {
    $user = User::factory()->create(array_merge([
        'email' => fake()->unique()->safeEmail(),
        'password' => 'Password123!',
        'tenant_id' => skOfficialTenantId(),
        'barangay_id' => skOfficialBarangayId(),
        'role' => User::ROLE_SK_OFFICIAL,
        'status' => User::STATUS_ACTIVE,
        'email_verified_at' => now(),
    ], $overrides));

    from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ])->assertRedirect('/login');

    assertGuest();
})->with([
    [['status' => User::STATUS_INACTIVE]],
    [['status' => User::STATUS_SUSPENDED]],
    [['barangay_id' => null]],
]);

it('blocks unverified officials and sends a verification email', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'unverified-official@example.com',
        'password' => 'Password123!',
        'tenant_id' => skOfficialTenantId(),
        'barangay_id' => skOfficialBarangayId(),
        'role' => User::ROLE_SK_OFFICIAL,
        'status' => User::STATUS_ACTIVE,
        'email_verified_at' => null,
    ]);

    from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ])->assertRedirect('/login');

    assertGuest();
    expect(session()->has('sk_official_email_verification_pending'))->toBeTrue();
    Notification::assertSentTo($user, VerifyEmail::class);
});

it('redirects first-login officials to change password until they update it', function () {
    $user = User::factory()->create([
        'email' => 'must-change@example.com',
        'password' => 'Password123!',
        'tenant_id' => skOfficialTenantId(),
        'barangay_id' => skOfficialBarangayId(),
        'role' => User::ROLE_SK_OFFICIAL,
        'status' => User::STATUS_ACTIVE,
        'email_verified_at' => now(),
        'must_change_password' => true,
    ]);

    post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ])->assertRedirect('/dashboard');

    get('/dashboard')->assertRedirect('/change-password');

    post('/change-password', [
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ])->assertRedirect('/login');

    expect((bool) $user->fresh()->must_change_password)->toBeFalse();
});
