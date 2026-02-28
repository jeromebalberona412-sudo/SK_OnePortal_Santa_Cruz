<?php

namespace App\Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceVerificationToken extends Model
{
    protected $table = 'sk_fed_device_verification_tokens';

    protected $fillable = [
        'user_id',
        'token_hash',
        'fingerprint',
        'ip_address',
        'user_agent',
        'expires_at',
        'used_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
