<?php

namespace App\Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class LoginAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email', 'ip_address', 'user_agent', 'successful', 'metadata', 'attempted_at',
    ];

    protected function casts(): array
    {
        return [
            'successful' => 'boolean',
            'metadata' => 'array',
            'attempted_at' => 'datetime',
        ];
    }

    public function scopeRecentByEmail($query, string $email, int $minutes = 15)
    {
        return $query->where('email', $email)
            ->where('successful', false)
            ->where('attempted_at', '>=', Carbon::now()->subMinutes($minutes));
    }

    public function scopeRecentByIp($query, string $ip, int $minutes = 15)
    {
        return $query->where('ip_address', $ip)
            ->where('successful', false)
            ->where('attempted_at', '>=', Carbon::now()->subMinutes($minutes));
    }

    public static function pruneOldAttempts(int $days = 30): int
    {
        return static::where('attempted_at', '<', Carbon::now()->subDays($days))->delete();
    }
}
