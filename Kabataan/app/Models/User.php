<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'tenant_id',
        'barangay_id',
        'role',
        'status',
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
            'email_change_token_expires_at' => 'datetime',
            'email_change_verified_at' => 'datetime',
            'email_change_last_sent_at' => 'datetime',
            'password_change_token_expires_at' => 'datetime',
            'password_change_last_sent_at' => 'datetime',
            'must_change_password' => 'boolean',
        ];
    }
}
