<?php

use App\Models\User;
use App\Modules\Authentication\Notifications\SkOfficialEmailVerificationNotification;
use App\Modules\Authentication\Services\TrustedDeviceService;
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
        $existing = DB::table('tenants')->where('code', config('sk_official_auth.tenant_code'))->value('id');

        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('tenants')->insertGetId([
            'code' => config('sk_official_auth.tenant_code'),
            'name' => 'Santa Cruz',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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

function skOfficialRememberUser(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'email' => 'remember-official@example.com',
        'password' => 'Password123!',
        'tenant_id' => skOfficialTenantId(),
        'barangay_id' => skOfficialBarangayId(),
        'role' => User::ROLE_SK_OFFICIAL,
        'status' => User::STATUS_ACTIVE,
        'email_verified_at' => now(),
        'must_change_password' => false,
    ], $overrides));
}

function responseHasRememberCookie($response): bool
{
    foreach ($response->headers->getCookies() as $cookie) {
        if (str_starts_with($cookie->getName(), 'remember_web')) {
            return true;
        }
    }

    return false;
}

it('shows the remember me checkbox on the login page', function () {
    get('/login')
        ->assertOk()
        ->assertSee('Remember me')
        ->assertDontSee('Remember me on this device')
        ->assertSee('Forgot password')
        ->assertDontSee('Forgot password?')
        ->assertSee('Remember this device?');
});

it('logs in without remember me and does not set a remember cookie', function () {
    $user = skOfficialRememberUser();

    $response = post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
    ]);

    $response->assertRedirect('/dashboard');
    assertAuthenticatedAs($user);
    expect(responseHasRememberCookie($response))->toBeFalse();
});

it('logs a verified official in with remember me and goes to the dashboard', function () {
    Notification::fake();
    $user = skOfficialRememberUser();

    $response = from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
        'remember' => '1',
    ]);

    $response->assertRedirect('/dashboard');
    assertAuthenticatedAs($user);
    expect(responseHasRememberCookie($response))->toBeTrue();
    Notification::assertNothingSent();
});

it('does not send account email verification when a verified user enables remember me', function () {
    Notification::fake();
    $user = skOfficialRememberUser();

    post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
        'remember' => '1',
    ])->assertRedirect('/dashboard');

    Notification::assertNotSentTo($user, SkOfficialEmailVerificationNotification::class);
});

it('directs unverified officials through account email verification instead of remember me', function () {
    Notification::fake();
    $user = skOfficialRememberUser([
        'email' => 'unverified-remember@example.com',
        'email_verified_at' => null,
    ]);

    from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
        'remember' => '1',
    ])->assertRedirect(route('sk_official.verification.wait'));

    assertGuest();
    expect(session()->has('sk_official_email_verification_pending'))->toBeTrue();
    Notification::assertSentTo($user, SkOfficialEmailVerificationNotification::class);
});

it('invalidates remember me on logout', function () {
    $user = skOfficialRememberUser();

    post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
        'remember' => '1',
    ])->assertRedirect('/dashboard');

    $this->post('/logout')->assertRedirect('/login');

    assertGuest();
    get('/dashboard')->assertRedirect('/login');
});

it('uses a seven day remember lifetime from configuration', function () {
    expect((int) config('sk_official_auth.remember.lifetime_days'))->toBe(7);
    expect(app(TrustedDeviceService::class)->expirationDays())->toBe(7);
});

it('rejects invalid credentials even when remember me is checked', function () {
    Notification::fake();
    $user = skOfficialRememberUser();

    from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'WrongPassword123!',
        'remember' => '1',
    ])->assertRedirect('/login');

    assertGuest();
    Notification::assertNothingSent();
});

it('prevents remembered access after an official account is disabled', function () {
    $user = skOfficialRememberUser();

    post('/login', [
        'email' => $user->email,
        'password' => 'Password123!',
        'remember' => '1',
    ])->assertRedirect('/dashboard');

    $user->forceFill(['status' => User::STATUS_INACTIVE])->save();

    get('/dashboard')->assertRedirect('/login');
    assertGuest();
});
