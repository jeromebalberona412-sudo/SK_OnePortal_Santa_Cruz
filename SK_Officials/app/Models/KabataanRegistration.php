<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KabataanRegistration extends Model
{
    protected $table = 'kabataan_registrations';

    protected $fillable = [
        'tenant_id', 'barangay_id', 'user_id', 'reviewed_by_user_id',
        'last_name', 'first_name', 'middle_name', 'suffix',
        'email', 'contact_number', 'form_data', 'status',
        'evaluation_status', 'evaluation_notes',
        'submitted_at', 'email_verified_at', 'password_set_at',
        'reviewed_at', 'review_notes',
    ];

    protected $casts = [
        'form_data'          => 'array',
        'evaluation_notes'   => 'array',
        'submitted_at'       => 'datetime',
        'email_verified_at'  => 'datetime',
        'password_set_at'    => 'datetime',
        'reviewed_at'        => 'datetime',
    ];

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function scopeForBarangay($query, int $barangayId)
    {
        return $query->where('barangay_id', $barangayId);
    }

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->last_name, $this->first_name, $this->middle_name]);
        $name = implode(', ', array_filter([$this->last_name, trim($this->first_name . ' ' . $this->middle_name)]));
        if ($this->suffix) $name .= ', ' . $this->suffix;
        return $name ?: '—';
    }
}
