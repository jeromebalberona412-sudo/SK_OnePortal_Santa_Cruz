<?php

namespace App\Modules\Shared\Models;

use App\Modules\Accounts\Models\Barangay;
use App\Modules\Accounts\Models\OfficialProfile;
use App\Modules\Authentication\Notifications\SkFedResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_SK_FED = 'sk_fed';

    public const ROLE_SK_OFFICIAL = 'sk_official';

    public const ROLE_USER = 'user';

    public const STATUS_ACTIVE = 'ACTIVE';

    public const STATUS_INACTIVE = 'INACTIVE';

    public const STATUS_PENDING_APPROVAL = 'PENDING_APPROVAL';

    public const STATUS_SUSPENDED = 'SUSPENDED';

    /**
     * Cached list of available table columns.
     *
     * @var array<int, string>|null
     */
    protected static ?array $columnCache = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'tenant_id',
        'status',
        'lockout_count',
        'lockout_until',
        'last_login_at',
        'last_login_ip',
        'active_session_id',
        'last_seen',
        'active_device',
        'last_ip',
        'barangay_id',
        'has_federation_access',
        'pending_email',
        'email_change_token',
        'email_change_token_expires_at',
        'email_change_verified_at',
        'email_change_last_sent_at',
        'pending_password',
        'password_change_token',
        'password_change_token_expires_at',
        'password_change_last_sent_at',
        'must_change_password',
        'account_status',
        'turnover_status',
        'activated_term_id',
        'turnover_notice_dismissed_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'lockout_until' => 'datetime',
            'last_login_at' => 'datetime',
            'last_seen' => 'datetime',
            'email_change_token_expires_at' => 'datetime',
            'email_change_verified_at' => 'datetime',
            'email_change_last_sent_at' => 'datetime',
            'password_change_token_expires_at' => 'datetime',
            'password_change_last_sent_at' => 'datetime',
            'must_change_password' => 'boolean',
            'has_federation_access' => 'boolean',
            'deleted_at' => 'datetime',
            'turnover_notice_dismissed_until' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            $driver = config('database.connections.'.config('database.default').'.driver');

            if ($driver !== 'pgsql') {
                return;
            }

            foreach (['has_federation_access', 'must_change_password'] as $booleanColumn) {
                if (! $user->isDirty($booleanColumn)) {
                    continue;
                }

                $enabled = (bool) $user->{$booleanColumn};
                $user->attributes[$booleanColumn] = DB::raw($enabled ? 'true' : 'false');
            }
        });
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function officialProfile(): HasOne
    {
        return $this->hasOne(OfficialProfile::class);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<static>  $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public function scopeHasFederationAccess($query)
    {
        $driver = config('database.connections.'.config('database.default').'.driver');

        if ($driver === 'pgsql') {
            return $query->whereRaw('has_federation_access IS TRUE');
        }

        return $query->where('has_federation_access', true);
    }

    public function setHasFederationAccess(bool $enabled): void
    {
        $this->has_federation_access = $enabled;
        $this->save();
    }

    public function hasRole(string ...$roles): bool
    {
        if (! $this->hasTableColumn('role')) {
            return false;
        }

        return in_array($this->role, $roles, true);
    }

    public function isSkFed(): bool
    {
        return $this->hasRole(self::ROLE_SK_FED);
    }

    public function canAccessFederationPortal(): bool
    {
        if ($this->isSkFed()) {
            return true;
        }

        if (! $this->hasRole(self::ROLE_SK_OFFICIAL) || ! (bool) $this->has_federation_access) {
            return false;
        }

        $this->loadMissing('officialProfile');
        $federationPosition = trim((string) ($this->officialProfile?->federation_position ?? ''));

        return in_array($federationPosition, OfficialProfile::FEDERATION_PORTAL_ACCESS_POSITIONS, true);
    }

    public function hasFederationLeadershipAccess(): bool
    {
        return $this->canAccessFederationPortal() && $this->hasRole(self::ROLE_SK_OFFICIAL);
    }

    public function isFederationAdministrator(): bool
    {
        return $this->isSkFed() || $this->hasFederationLeadershipAccess();
    }

    public function isIncomingTurnoverOfficer(): bool
    {
        if (in_array($this->turnover_status, ['awaiting_setup', 'pending_confirmation'], true)) {
            return true;
        }

        return in_array($this->account_status, ['turnover_pending', 'turnover_waiting'], true);
    }

    public function canLoginToPortal(): bool
    {
        if ($this->isIncomingTurnoverOfficer()) {
            return false;
        }

        if ($this->turnover_status === 'archived') {
            return false;
        }

        return true;
    }

    public function isAdmin(): bool
    {
        return $this->isFederationAdministrator();
    }

    public function isLocked(): bool
    {
        if (! $this->hasTableColumn('lockout_until')) {
            return false;
        }

        return $this->lockout_until !== null && $this->lockout_until->isFuture();
    }

    public function incrementLockout(int $minutes): void
    {
        if (! $this->hasTableColumn('lockout_count') || ! $this->hasTableColumn('lockout_until')) {
            return;
        }

        $this->forceFill([
            'lockout_count' => ((int) $this->lockout_count) + 1,
            'lockout_until' => now()->addMinutes($minutes),
        ])->save();
    }

    public function resetLockout(): void
    {
        if (! $this->hasTableColumn('lockout_count') || ! $this->hasTableColumn('lockout_until')) {
            return;
        }

        $this->forceFill([
            'lockout_count' => 0,
            'lockout_until' => null,
        ])->save();
    }

    public function recordLogin(string $ipAddress): void
    {
        $attributes = [];

        if ($this->hasTableColumn('last_login_at')) {
            $attributes['last_login_at'] = now();
        }

        if ($this->hasTableColumn('last_login_ip')) {
            $attributes['last_login_ip'] = $ipAddress;
        }

        if ($attributes !== []) {
            $this->forceFill($attributes)->save();
        }
    }

    public function sendEmailVerificationNotification(): void
    {
        $notification = new VerifyEmail;
        $notification->createUrlUsing(function ($notifiable) {
            return URL::temporarySignedRoute(
                'skfed.verification.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 10)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );
        });
        $notification->toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('Verify Your SK Federation Account')
                ->greeting('Hello!')
                ->line('Please verify your SK Federation account to complete secure access setup.')
                ->action('Verify Email Address', $url)
                ->line('This verification link expires shortly for your security.')
                ->line('If you did not request this, no further action is required.');
        });

        $deliveryResult = self::deliverWithFallback(fn () => $this->notify($notification));

        Log::info('Email verification notification dispatch result', [
            'user_id' => $this->getKey(),
            'email' => $this->email,
            'type' => 'email_verification',
            'delivered' => $deliveryResult['delivered'],
            'fallback_used' => $deliveryResult['fallback_used'],
            'error' => $deliveryResult['error'],
            'exception_class' => $deliveryResult['exception_class'],
        ]);

        self::setLastDeliveryResult($deliveryResult);
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $deliveryResult = self::deliverWithFallback(fn () => $this->notify(new SkFedResetPasswordNotification($token)));

        Log::info('Password reset notification dispatch result', [
            'user_id' => $this->getKey(),
            'email' => $this->email,
            'type' => 'password_reset',
            'delivered' => $deliveryResult['delivered'],
            'fallback_used' => $deliveryResult['fallback_used'],
            'error' => $deliveryResult['error'],
            'exception_class' => $deliveryResult['exception_class'],
        ]);

        self::setLastDeliveryResult($deliveryResult);
    }

    /**
     * Deliver notification with explicit handling so the caller can
     * distinguish "actually delivered" from "written to log fallback".
     *
     * The Laravel `failover` mailer will silently write to the log driver
     * when upstream transports (smtp/resend) fail. To the caller this
     * looks like "success", so here we track whether any real transport
     * was attempted before we let the failover chain proceed.
     *
     * @param  callable  $sender  function that will trigger `notify()`
     * @return array{delivered:bool,fallback_used:bool,error:?string,exception_class:?string}
     */
    protected static function deliverWithFallback(callable $sender): array
    {
        $realMailers = ['smtp', 'resend', 'postmark', 'ses', 'mailgun', 'ses-v2', 'sendmail'];
        $configuredMailer = (string) config('mail.default', '');
        $failoverMailers = config('mail.mailers.failover.mailers');
        $mailerCandidates = is_array($failoverMailers)
            ? $failoverMailers
            : [$configuredMailer];

        $hasRealTransport = false;
        foreach ($mailerCandidates as $candidate) {
            if (in_array((string) $candidate, $realMailers, true)) {
                $transportConfig = config('mail.mailers.'.(string) $candidate);
                if (! is_array($transportConfig)) {
                    continue;
                }

                $transport = (string) ($transportConfig['transport'] ?? (string) $candidate);
                if (! in_array($transport, $realMailers, true)) {
                    continue;
                }

                if ($transport === 'smtp') {
                    $host = trim((string) ($transportConfig['host'] ?? ''));
                    $user = trim((string) ($transportConfig['username'] ?? ''));
                    if ($host !== '' && $user !== '' && $host !== '127.0.0.1' && $host !== 'localhost') {
                        $hasRealTransport = true;
                        break;
                    }
                } elseif ($transport === 'resend') {
                    $key = trim((string) config('services.resend.key', ''));
                    if ($key !== '') {
                        $hasRealTransport = true;
                        break;
                    }
                } else {
                    $hasRealTransport = true;
                    break;
                }
            }
        }

        $result = [
            'delivered' => ! $hasRealTransport,
            'fallback_used' => ! $hasRealTransport,
            'error' => null,
            'exception_class' => null,
        ];

        try {
            $sender();
            if ($hasRealTransport) {
                $result['delivered'] = true;
                $result['fallback_used'] = false;
            }
        } catch (\Throwable $e) {
            Log::error('Mail transport exception caught during dispatch', [
                'error' => $e->getMessage(),
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $result['delivered'] = false;
            $result['fallback_used'] = true;
            $result['error'] = $e->getMessage();
            $result['exception_class'] = get_class($e);
        }

        return $result;
    }

    /**
     * In-request memory for the most recent notification delivery result so
     * the controller/service layer can report status accurately.
     *
     * @var array{delivered:bool,fallback_used:bool,error:?string,exception_class:?string}|null
     */
    protected static ?array $_lastDeliveryResult = null;

    /**
     * Thread-safe delivery result slot for the current request.
     *
     * @return array{delivered:bool,fallback_used:bool,error:?string,exception_class:?string}
     */
    public static function lastDeliveryResult(): array
    {
        return self::$_lastDeliveryResult ?? [
            'delivered' => false,
            'fallback_used' => false,
            'error' => null,
            'exception_class' => null,
        ];
    }

    /**
     * @param  array{delivered:bool,fallback_used:bool,error:?string,exception_class:?string}  $result
     */
    protected static function setLastDeliveryResult(array $result): void
    {
        self::$_lastDeliveryResult = $result;
    }

    protected function hasTableColumn(string $column): bool
    {
        if (static::$columnCache === null) {
            try {
                static::$columnCache = Schema::getColumnListing($this->getTable());
            } catch (\Throwable) {
                static::$columnCache = [];
            }
        }

        return in_array($column, static::$columnCache, true);
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
