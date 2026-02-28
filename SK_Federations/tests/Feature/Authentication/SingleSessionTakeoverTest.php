<?php

use App\Modules\Authentication\Models\FeatureFlag;
use App\Modules\Authentication\Notifications\SessionTakeoverOtpNotification;
use App\Modules\Shared\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\from;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('session.driver', 'database');
    FeatureFlag::query()->where('flag_key', 'features.device_verification')->update(['enabled' => false]);
});

function skFedTenantId(): int
{
    return (int) DB::table('tenants')->where('code', config('sk_fed_auth.tenant_code'))->value('id');
}

it('blocks concurrent login and starts OTP takeover challenge when an active session exists', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'active-session@example.com',
        'password' => 'Password123!',
        'tenant_id' => skFedTenantId(),
        'role' => User::ROLE_SK_FED,
        'email_verified_at' => now(),
        'active_session_id' => 'existing-session-id',
        'last_seen' => now(),
    ]);

    DB::table('sessions')->insert([
        'id' => 'existing-session-id',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Existing Device',
        'payload' => 'test',
        'last_activity' => now()->timestamp,
    ]);

    from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ])->assertRedirect('/login');

    expect(session()->has('sk_fed_takeover_pending'))->toBeTrue();
    assertGuest();

    post(route('skfed.takeover.send'))->assertRedirect(route('skfed.takeover.wait'));

    Notification::assertSentTo($user, SessionTakeoverOtpNotification::class);
});

it('allows login automatically when previous session is inactive', function () {
    $user = User::factory()->create([
        'email' => 'inactive-session@example.com',
        'password' => 'Password123!',
        'tenant_id' => skFedTenantId(),
        'role' => User::ROLE_SK_FED,
        'email_verified_at' => now(),
        'active_session_id' => 'stale-session-id',
        'last_seen' => now()->subMinutes(3),
    ]);

    DB::table('sessions')->insert([
        'id' => 'stale-session-id',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Stale Device',
        'payload' => 'test',
        'last_activity' => now()->timestamp,
    ]);

    post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ])->assertRedirect('/dashboard');

    assertAuthenticatedAs($user);
});

it('approves OTP takeover, terminates old session, and creates new secure session', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'takeover@example.com',
        'password' => 'Password123!',
        'tenant_id' => skFedTenantId(),
        'role' => User::ROLE_SK_FED,
        'email_verified_at' => now(),
        'active_session_id' => 'attacker-session-id',
        'last_seen' => now(),
    ]);

    DB::table('sessions')->insert([
        'id' => 'attacker-session-id',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Attacker Device',
        'payload' => 'test',
        'last_activity' => now()->timestamp,
    ]);

    from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ])->assertRedirect('/login');

    post(route('skfed.takeover.send'))->assertRedirect(route('skfed.takeover.wait'));

    $otpCode = '';

    Notification::assertSentTo($user, SessionTakeoverOtpNotification::class, function (SessionTakeoverOtpNotification $notification) use (&$otpCode): bool {
        $otpCode = $notification->otpCode();

        return true;
    });

    expect($otpCode)->toHaveLength(6);

    post(route('skfed.takeover.verify'), [
        'otp_code' => $otpCode,
    ])->assertRedirect('/dashboard');

    assertAuthenticatedAs($user);
    expect(DB::table('sessions')->where('id', 'attacker-session-id')->exists())->toBeFalse();

    $user->refresh();
    expect((string) $user->active_session_id)
        ->not->toBe('')
        ->and($user->active_session_id)->not->toBe('attacker-session-id')
        ->and($user->last_seen)->not->toBeNull();
});

it('updates last_seen via heartbeat endpoint', function () {
    $user = User::factory()->create([
        'tenant_id' => skFedTenantId(),
        'role' => User::ROLE_SK_FED,
        'email_verified_at' => now(),
        'last_seen' => now()->subMinutes(5),
    ]);

    actingAs($user)
        ->post(route('skfed.heartbeat'))
        ->assertOk()
        ->assertJson(['ok' => true]);

    $user->refresh();

    expect($user->last_seen)->not->toBeNull();
    expect($user->last_seen->greaterThan(now()->subMinute()))->toBeTrue();
});

it('does not allow heartbeat to steal ownership from another active session', function () {
    $user = User::factory()->create([
        'tenant_id' => skFedTenantId(),
        'role' => User::ROLE_SK_FED,
        'email_verified_at' => now(),
        'active_session_id' => 'owner-session-id',
        'last_seen' => now(),
    ]);

    DB::table('sessions')->insert([
        'id' => 'owner-session-id',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Owner Device',
        'payload' => 'test',
        'last_activity' => now()->timestamp,
    ]);

    actingAs($user)
        ->post(route('skfed.heartbeat'))
        ->assertRedirect('/login');

    $user->refresh();
    expect($user->active_session_id)->toBe('owner-session-id');
});

it('forces logout when a different active session owns the account', function () {
    $user = User::factory()->create([
        'tenant_id' => skFedTenantId(),
        'role' => User::ROLE_SK_FED,
        'email_verified_at' => now(),
        'active_session_id' => 'other-active-session',
        'last_seen' => now(),
    ]);

    DB::table('sessions')->insert([
        'id' => 'other-active-session',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Another Device',
        'payload' => 'test',
        'last_activity' => now()->timestamp,
    ]);

    actingAs($user)
        ->get('/dashboard')
        ->assertRedirect('/login');

    assertGuest();
});
