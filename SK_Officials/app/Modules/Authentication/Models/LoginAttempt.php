<?php

namespace App\Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    public $timestamps = false;

    protected $table = 'sk_official_login_attempts';

    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'successful',
        'user_agent',
        'attempted_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'successful' => 'boolean',
            'attempted_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function scopeRecentFailedByEmail(Builder $query, string $email, int $windowMinutes): Builder
    {
        return $query
            ->where('email', $email)
            ->where('successful', false)
            ->where('attempted_at', '>=', now()->subMinutes($windowMinutes));
    }

    public function scopeRecentFailedByIp(Builder $query, string $ipAddress, int $windowMinutes): Builder
    {
        return $query
            ->where('ip_address', $ipAddress)
            ->where('successful', false)
            ->where('attempted_at', '>=', now()->subMinutes($windowMinutes));
    }
}
