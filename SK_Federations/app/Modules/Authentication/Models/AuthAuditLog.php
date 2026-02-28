<?php

namespace App\Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;

class AuthAuditLog extends Model
{
    protected $table = 'sk_fed_auth_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
