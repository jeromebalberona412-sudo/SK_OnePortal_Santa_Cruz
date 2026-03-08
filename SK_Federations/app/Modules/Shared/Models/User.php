<?php

namespace App\Modules\Shared\Models;

use App\Modules\Authentication\Notifications\SkFedResetPasswordNotification;
use App\Modules\Authentication\Notifications\SkFedVerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_SK_FED = 'sk_fed';

    public const ROLE_SK_OFFICIAL = 'sk_official';

    public const ROLE_USER = 'user';

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
        'otp_code',
        'otp_expires_at',
        'otp_attempts',
        'otp_last_sent_at',
        'barangay_id',
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
            'otp_expires_at' => 'datetime',
            'otp_last_sent_at' => 'datetime',
        ];
    }

    public function hasRole(string ...$roles): bool
    {
        if (! $this->hasTableColumn('role')) {
            return false;
        }

        return in_array($this->role, $roles, true);
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
        $this->notify(new SkFedVerifyEmailNotification);
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new SkFedResetPasswordNotification($token));
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
