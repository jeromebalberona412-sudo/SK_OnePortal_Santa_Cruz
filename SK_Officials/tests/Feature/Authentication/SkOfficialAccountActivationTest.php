<?php

use App\Models\User;
use App\Modules\Authentication\Notifications\SkOfficialAccountActivationNotification;
use App\Modules\Authentication\Notifications\SkOfficialResetPasswordNotification;
use App\Modules\Authentication\Services\AccountActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

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

function createPendingOfficial(string $email): User
{
    return User::factory()->create([
        'email' => $email,
        'password' => Hash::make('TemporaryPass123!'),
        'tenant_id' => skOfficialTenantId(),
        'barangay_id' => skOfficialBarangayId(),
        'role' => User::ROLE_SK_OFFICIAL,
        'status' => User::STATUS_PENDING_APPROVAL,
        'email_verified_at' => null,
        'must_change_password' => true,
        'name' => 'Juan Official',
    ]);
}

function createActiveOfficial(string $email): User
{
    return User::factory()->create([
        'email' => $email,
        'password' => Hash::make('Password123!'),
        'tenant_id' => skOfficialTenantId(),
        'barangay_id' => skOfficialBarangayId(),
        'role' => User::ROLE_SK_OFFICIAL,
        'status' => User::STATUS_ACTIVE,
        'email_verified_at' => now(),
        'must_change_password' => false,
    ]);
}

it('shows activate account on the login page', function () {
    get(route('login', [], false))
        ->assertSuccessful()
        ->assertSee('Activate Account')
        ->assertSee(route('account.activation.request', [], false), false);
});

it('renders the activate account request page', function () {
    get(route('account.activation.request', [], false))
        ->assertSuccessful()
        ->assertSee('Activate Account')
        ->assertSee('Send Activation Link');
});

it('sends a new activation email for a pending official and invalidates the previous token', function () {
    Notification::fake();

    $official = createPendingOfficial('juan@gmail.com');
    $oldToken = Password::createToken($official);

    post(route('account.activation.send', [], false), [
        'email' => $official->email,
    ])->assertRedirect(route('account.activation.sent', [], false));

    Notification::assertSentTo($official, SkOfficialAccountActivationNotification::class);
    expect(Password::tokenExists($official, $oldToken))->toBeFalse();
    expect(DB::table('password_reset_tokens')->where('email', $official->email)->exists())->toBeTrue();

    get(route('account.activation.sent', [], false))
        ->assertSuccessful()
        ->assertSee('Resend activation email')
        ->assertSee('Cancel')
        ->assertDontSee('If the email address is registered and eligible');
});

it('shows an invalid email error instead of the check-email page', function () {
    Notification::fake();

    post(route('account.activation.send', [], false), [
        'email' => 'missing@example.com',
    ])->assertSessionHasErrors('email', AccountActivationService::INVALID_MESSAGE);

    Notification::assertNothingSent();
});

it('does not send an activation email for an already active official', function () {
    Notification::fake();

    $official = createActiveOfficial('active-official@example.com');

    post(route('account.activation.send', [], false), [
        'email' => $official->email,
    ])->assertRedirect(route('account.activation.already-active', [], false));

    Notification::assertNothingSent();
    expect(DB::table('password_reset_tokens')->where('email', $official->email)->exists())->toBeFalse();
});

it('does not send an activation email for a disabled official', function () {
    Notification::fake();

    $official = createPendingOfficial('disabled-official@example.com');
    $official->forceFill(['status' => User::STATUS_INACTIVE])->save();

    post(route('account.activation.send', [], false), [
        'email' => $official->email,
    ])->assertRedirect(route('account.activation.sent', [], false));

    Notification::assertNothingSent();
});

it('does not send an activation email for a federation account', function () {
    Notification::fake();

    $federation = User::factory()->create([
        'email' => 'federation@example.com',
        'password' => Hash::make('Password123!'),
        'tenant_id' => skOfficialTenantId(),
        'barangay_id' => skOfficialBarangayId(),
        'role' => User::ROLE_SK_FED,
        'status' => User::STATUS_PENDING_APPROVAL,
        'email_verified_at' => null,
        'must_change_password' => true,
    ]);

    post(route('account.activation.send', [], false), [
        'email' => $federation->email,
    ])->assertRedirect(route('account.activation.sent', [], false));

    Notification::assertNothingSent();
});

it('activates a pending official with a valid token and prevents reuse', function () {
    $official = createPendingOfficial('activate-success@example.com');
    $token = Password::createToken($official);

    from(route('account.activation.show', ['token' => $token, 'email' => $official->email], false))
        ->post(route('account.activation.activate', [], false), [
            'token' => $token,
            'email' => $official->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])
        ->assertRedirect(route('login', [], false));

    $official->refresh();

    expect(Hash::check('NewPassword123!', $official->password))->toBeTrue();
    expect($official->status)->toBe(User::STATUS_ACTIVE);
    expect($official->email_verified_at)->not->toBeNull();
    expect((bool) $official->must_change_password)->toBeFalse();
    expect(DB::table('password_reset_tokens')->where('email', $official->email)->exists())->toBeFalse();

    get(route('account.activation.show', ['token' => $token, 'email' => $official->email], false))
        ->assertSuccessful()
        ->assertSee('already been activated');
});

it('shows an expired activation page after the activation window', function () {
    $official = createPendingOfficial('expired-link@example.com');
    $token = Password::createToken($official);

    $this->travel(2)->days();

    get(route('account.activation.show', ['token' => $token, 'email' => $official->email], false))
        ->assertSuccessful()
        ->assertSee('Activation Link Expired')
        ->assertSee('Activate Account')
        ->assertSee('Back to Login');
});

it('throttles repeated activation requests for the same email', function () {
    Notification::fake();
    Cache::flush();

    $official = createPendingOfficial('cooldown@example.com');

    post(route('account.activation.send', [], false), [
        'email' => $official->email,
    ])->assertRedirect(route('account.activation.sent', [], false));

    post(route('account.activation.send', [], false), [
        'email' => $official->email,
    ])->assertRedirect(route('account.activation.sent', [], false));

    Notification::assertSentTimes(SkOfficialAccountActivationNotification::class, 1);
});

it('keeps forgot password available for active officials only', function () {
    Notification::fake();

    $active = createActiveOfficial('reset-active@example.com');
    $pending = createPendingOfficial('reset-pending@example.com');

    post(route('password.email', [], false), [
        'email' => $active->email,
    ])->assertSessionHas('status');

    Notification::assertSentTo($active, SkOfficialResetPasswordNotification::class);

    post(route('password.email', [], false), [
        'email' => $pending->email,
    ]);

    Notification::assertNotSentTo($pending, SkOfficialResetPasswordNotification::class);
    Notification::assertNotSentTo($pending, SkOfficialAccountActivationNotification::class);
});
