<?php

use App\Models\User;
use App\Modules\Authentication\Notifications\SkOfficialResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

use function Pest\Laravel\from;
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

it('sends a reset link only for in-scope sk officials', function () {
    Notification::fake();

    $tenantId = skOfficialTenantId();
    $barangayId = skOfficialBarangayId();

    $official = User::factory()->create([
        'email' => 'reset-official@example.com',
        'password' => 'Password123!',
        'tenant_id' => $tenantId,
        'barangay_id' => $barangayId,
        'role' => User::ROLE_SK_OFFICIAL,
        'status' => User::STATUS_ACTIVE,
        'email_verified_at' => now(),
    ]);

    $outsider = User::factory()->create([
        'email' => 'reset-outsider@example.com',
        'password' => 'Password123!',
        'tenant_id' => $tenantId,
        'barangay_id' => $barangayId,
        'role' => User::ROLE_SK_FED,
        'status' => User::STATUS_ACTIVE,
        'email_verified_at' => now(),
    ]);

    post(route('password.email', [], false), [
        'email' => $official->email,
    ])->assertSessionHas('status', 'A password reset link has been sent');

    post(route('password.email', [], false), [
        'email' => $outsider->email,
    ])->assertSessionHas('status', 'A password reset link has been sent');

    Notification::assertSentTo($official, SkOfficialResetPasswordNotification::class);
    Notification::assertNotSentTo($outsider, SkOfficialResetPasswordNotification::class);
    expect(DB::table('password_reset_tokens')->where('email', $official->email)->exists())->toBeTrue();
    expect(DB::table('password_reset_tokens')->where('email', $outsider->email)->exists())->toBeFalse();
});

it('resets an official password with a valid token', function () {
    $official = User::factory()->create([
        'email' => 'reset-success@example.com',
        'password' => 'Password123!',
        'tenant_id' => skOfficialTenantId(),
        'barangay_id' => skOfficialBarangayId(),
        'role' => User::ROLE_SK_OFFICIAL,
        'status' => User::STATUS_ACTIVE,
        'email_verified_at' => now(),
        'must_change_password' => true,
    ]);

    $token = Password::createToken($official);

    from(route('password.reset', ['token' => $token, 'email' => $official->email], false))
        ->post(route('password.update', [], false), [
            'token' => $token,
            'email' => $official->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])
        ->assertRedirect(route('password.reset.success', [], false));

    $official->refresh();

    expect(Hash::check('NewPassword123!', $official->password))->toBeTrue();
    expect((bool) $official->must_change_password)->toBeFalse();
    expect(DB::table('password_reset_tokens')->where('email', $official->email)->exists())->toBeFalse();
});
