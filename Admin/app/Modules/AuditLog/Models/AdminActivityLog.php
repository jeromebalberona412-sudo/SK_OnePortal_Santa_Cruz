<?php

namespace App\Modules\AuditLog\Models;

use App\Modules\Shared\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Modules\Shared\Models\User;

class AdminActivityLog extends Model
{
    /**
     * Disable updated_at timestamp (append-only log).
     */
    const UPDATED_AT = null;

    /**
     * The primary key type is UUID (string).
     */
    protected $keyType = 'string';

    /**
     * Primary key is not auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'tenant_id',
        'user_id',
        'event_type',
        'action',
        'entity_type',
        'entity_id',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Boot method to generate UUID for new records.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        // Prevent updates and deletes to maintain immutability
        static::updating(function ($model) {
            return false;
        });

        static::deleting(function ($model) {
            return false;
        });
    }

    /**
     * Relationship: Belongs to User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Event type constants for consistency.
     */
    const EVENT_LOGIN_SUCCESS = 'login_success';
    const EVENT_LOGIN_FAILED = 'login_failed';
    const EVENT_ACCOUNT_LOCKED = 'account_locked';
    const EVENT_2FA_CHALLENGE_FAILED = 'two_factor_challenge_failed';
    const EVENT_2FA_CHALLENGE_PASSED = 'two_factor_challenge_passed';
    const EVENT_2FA_ENABLED = 'two_factor_enabled';
    const EVENT_2FA_DISABLED = 'two_factor_disabled';
    const EVENT_PASSWORD_CHANGED = 'password_changed';
    const EVENT_PASSWORD_RESET_REQUESTED = 'password_reset_requested';
    const EVENT_LOGOUT = 'logout';
}
