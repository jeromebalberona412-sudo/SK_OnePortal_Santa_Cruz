<?php

namespace App\Modules\Shared\Models;

use App\Modules\Accounts\Models\Barangay;
use App\Modules\Accounts\Models\OfficialProfile;
use App\Modules\Shared\Models\Tenant;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_SUPER_ADMIN = 'SUPER_ADMIN';

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
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'role',
        'status',
        'barangay_id',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'two_factor_confirmed_at' => 'datetime',
            'lockout_until' => 'datetime',
            'last_login_at' => 'datetime',
            'must_change_password' => 'boolean',
            'deleted_at' => 'datetime',
            'password_change_last_sent_at' => 'datetime',
            'password_change_token_expires_at' => 'datetime',
            'email_change_token_expires_at' => 'datetime',
            'email_change_verified_at' => 'datetime',
            'email_change_last_sent_at' => 'datetime',
        ];
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

    public function hasRole(string ...$roles): bool
    {
        if (! $this->hasTableColumn('role')) {
            return false;
        }

        if (in_array($this->role, $roles, true)) {
            return true;
        }

        if ($this->role === self::ROLE_SUPER_ADMIN && in_array(self::ROLE_ADMIN, $roles, true)) {
            return true;
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->hasTableColumn('role')
            && in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN], true);
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function getEmailForVerification(): string
    {
        return (string) $this->email;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasTableColumn('role')
            && $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Check if the user account is currently locked.
     *
     * @return bool
     */
    public function getIsLockedAttribute(): bool
    {
        if (! $this->hasTableColumn('lockout_until')) {
            return false;
        }

        return $this->lockout_until && $this->lockout_until->isFuture();
    }

    /**
     * Increment the lockout count and set lockout duration.
     *
     * @param int $minutes Duration of lockout in minutes
     * @return void
     */
    public function incrementLockoutCount(int $minutes = 15): void
    {
        if (! $this->hasTableColumn('lockout_count') || ! $this->hasTableColumn('lockout_until')) {
            return;
        }

        $this->lockout_count = ($this->lockout_count ?? 0) + 1;
        $this->lockout_until = now()->addMinutes($minutes);
        $this->save();
    }

    /**
     * Reset lockout fields after successful authentication.
     *
     * @return void
     */
    public function resetLockout(): void
    {
        if (! $this->hasTableColumn('lockout_count') || ! $this->hasTableColumn('lockout_until')) {
            return;
        }

        if ($this->lockout_count > 0 || $this->lockout_until) {
            $this->lockout_count = 0;
            $this->lockout_until = null;
            $this->save();
        }
    }

    /**
     * Check if account requires password reset due to excessive lockouts.
     *
     * @return bool
     */
    public function requiresPasswordReset(): bool
    {
        if (! $this->hasTableColumn('lockout_count')) {
            return false;
        }

        return $this->lockout_count >= 3;
    }

    /**
     * Persist must_change_password using a PostgreSQL-safe boolean write.
     */
    public function setMustChangePassword(bool $value): void
    {
        if (! $this->hasTableColumn('must_change_password')) {
            return;
        }

        $storedValue = $this->booleanValueForDatabase($value);

        static::query()
            ->whereKey($this->getKey())
            ->update(['must_change_password' => $storedValue]);

        $this->setAttribute('must_change_password', $value);
        $this->syncOriginalAttribute('must_change_password');
    }

    public function clearMustChangePassword(): void
    {
        $this->setMustChangePassword(false);
    }

    /**
     * @return bool|\Illuminate\Contracts\Database\Query\Expression
     */
    protected function booleanValueForDatabase(bool $value): bool|\Illuminate\Database\Query\Expression
    {
        if (config('database.default') === 'pgsql') {
            return DB::raw($value ? 'true' : 'false');
        }

        return $value;
    }

    /**
     * Update last login timestamp and IP address.
     *
     * @param string $ipAddress
     * @return void
     */
    public function recordLogin(string $ipAddress): void
    {
        $updated = false;

        if ($this->hasTableColumn('last_login_at')) {
            $this->last_login_at = now();
            $updated = true;
        }

        if ($this->hasTableColumn('last_login_ip')) {
            $this->last_login_ip = $ipAddress;
            $updated = true;
        }

        if ($updated) {
            $this->save();
        }
    }

    /**
     * Check if a column exists on the users table.
     */
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
