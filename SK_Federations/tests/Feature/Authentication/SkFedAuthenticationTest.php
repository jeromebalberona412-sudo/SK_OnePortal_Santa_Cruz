<?php

use App\Modules\Authentication\Models\DeviceVerificationToken;
use App\Modules\Authentication\Models\EmailVerifiedDevice;
use App\Modules\Authentication\Models\FeatureFlag;
use App\Modules\Authentication\Models\TrustedDevice;
use App\Modules\Authentication\Notifications\DeviceVerificationNotification;
use App\Modules\Authentication\Notifications\SkFedVerifyEmailNotification;
use App\Modules\Shared\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

it('allows verified sk fed user login when device verification feature is disabled', function () {
    FeatureFlag::query()->where('flag_key', 'features.device_verification')->update(['enabled' => false]);

    $tenantId = (int) DB::table('tenants')->where('code', config('sk_fed_auth.tenant_code'))->value('id');

    $user = User::factory()->create([
        'email' => 'federation@example.com',
        'password' => 'Password123!',
        'tenant_id' => $tenantId,
        'role' => 'sk_fed',
        'email_verified_at' => now(),
    ]);

    $response = post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $response->assertRedirect('/dashboard');
    assertAuthenticatedAs($user);
});

it('blocks unverified user and sends verification email', function () {
    Notification::fake();
    FeatureFlag::query()->where('flag_key', 'features.device_verification')->update(['enabled' => false]);

    $tenantId = (int) DB::table('tenants')->where('code', config('sk_fed_auth.tenant_code'))->value('id');

    $user = User::factory()->create([
        'email' => 'unverified@example.com',
        'password' => 'Password123!',
        'tenant_id' => $tenantId,
        'role' => 'sk_fed',
        'email_verified_at' => null,
    ]);

    $response = from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $response->assertRedirect('/login');
    assertGuest();

    Notification::assertSentTo($user, SkFedVerifyEmailNotification::class);
});

it('challenges untrusted device and sends device verification notification', function () {
    Notification::fake();

    $tenantId = (int) DB::table('tenants')->where('code', config('sk_fed_auth.tenant_code'))->value('id');

    $user = User::factory()->create([
        'email' => 'untrusted@example.com',
        'password' => 'Password123!',
        'tenant_id' => $tenantId,
        'role' => 'sk_fed',
        'email_verified_at' => now(),
    ]);

    $response = from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $response->assertRedirect('/login');
    assertGuest();

    Notification::assertSentTo($user, DeviceVerificationNotification::class);
});

it('verifies device token and authenticates user', function () {
    $tenantId = (int) DB::table('tenants')->where('code', config('sk_fed_auth.tenant_code'))->value('id');

    $user = User::factory()->create([
        'email' => 'tokenverify@example.com',
        'password' => 'Password123!',
        'tenant_id' => $tenantId,
        'role' => 'sk_fed',
        'email_verified_at' => now(),
    ]);

    $plainToken = 'device-token-123';

    DeviceVerificationToken::query()->create([
        'user_id' => $user->id,
        'token_hash' => hash('sha256', $plainToken),
        'fingerprint' => hash('sha256', 'fingerprint'),
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest Test Agent',
        'expires_at' => now()->addMinutes(10),
    ]);

    $signedUrl = URL::temporarySignedRoute('device.verify', now()->addMinutes(10), [
        'user' => $user->id,
        'token' => $plainToken,
    ]);

    $response = get($signedUrl);

    $response->assertRedirect('/dashboard');
    assertAuthenticatedAs($user);

    expect(TrustedDevice::query()->where('user_id', $user->id)->exists())->toBeTrue();
});

it('requires email verification again when login device differs from the last verified device', function () {
    Notification::fake();
    FeatureFlag::query()->where('flag_key', 'features.device_verification')->update(['enabled' => false]);

    $tenantId = (int) DB::table('tenants')->where('code', config('sk_fed_auth.tenant_code'))->value('id');

    $user = User::factory()->create([
        'email' => 'devicechange@example.com',
        'password' => 'Password123!',
        'tenant_id' => $tenantId,
        'role' => 'sk_fed',
        'email_verified_at' => now(),
    ]);

    EmailVerifiedDevice::query()->create([
        'user_id' => $user->id,
        'fingerprint' => hash('sha256', 'old-device-fingerprint'),
        'verified_at' => now()->subDay(),
        'ip_address' => '192.168.1.10',
        'user_agent' => 'Old Device Agent',
    ]);

    $response = from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $response->assertRedirect('/login');
    assertGuest();
    expect(session()->has('sk_fed_email_verification_pending'))->toBeTrue();

    Notification::assertSentTo($user, SkFedVerifyEmailNotification::class);
});

it('redirects to dashboard from wait-status once an unverified user verifies email', function () {
    Notification::fake();
    FeatureFlag::query()->where('flag_key', 'features.device_verification')->update(['enabled' => false]);

    $tenantId = (int) DB::table('tenants')->where('code', config('sk_fed_auth.tenant_code'))->value('id');

    $user = User::factory()->create([
        'email' => 'waitstatus@example.com',
        'password' => 'Password123!',
        'tenant_id' => $tenantId,
        'role' => 'sk_fed',
        'email_verified_at' => null,
    ]);

    from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ])->assertRedirect('/login');

    $pending = session('sk_fed_email_verification_pending');
    expect($pending)->toBeArray()
        ->and($pending['requires_fresh_verification'] ?? null)->toBeFalse();

    $user->forceFill(['email_verified_at' => now()])->save();

    $response = getJson(route('skfed.verification.wait.status'));

    $response->assertOk()
        ->assertJson([
            'state' => 'verified',
            'redirect' => route('dashboard'),
        ]);
    assertAuthenticatedAs($user);
});

it('keeps wait-status pending for fresh verification until email_verified_at changes', function () {
    $tenantId = (int) DB::table('tenants')->where('code', config('sk_fed_auth.tenant_code'))->value('id');

    $baseline = now()->subHour()->startOfSecond();

    $user = User::factory()->create([
        'email' => 'freshverify@example.com',
        'password' => 'Password123!',
        'tenant_id' => $tenantId,
        'role' => 'sk_fed',
        'email_verified_at' => $baseline,
    ]);

    $this->withSession([
        'sk_fed_email_verification_pending' => [
            'user_id' => $user->id,
            'email' => $user->email,
            'started_at' => now()->subMinute()->toIso8601String(),
            'expires_at' => now()->addMinutes(10)->toIso8601String(),
            'requires_fresh_verification' => true,
            'verified_at_snapshot' => $baseline->toIso8601String(),
        ],
    ]);

    getJson(route('skfed.verification.wait.status'))
        ->assertOk()
        ->assertJson([
            'state' => 'pending',
        ]);

    $user->forceFill([
        'email_verified_at' => $baseline->copy()->addSecond(),
    ])->save();

    getJson(route('skfed.verification.wait.status'))
        ->assertOk()
        ->assertJson([
            'state' => 'verified',
            'redirect' => route('dashboard'),
        ]);
    assertAuthenticatedAs($user);
});
