<?php

namespace App\Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;

class AuthAuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'sk_official_auth_audit_logs';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'actor_email',
        'event',
        'outcome',
        'resource_type',
        'resource_id',
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
