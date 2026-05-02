<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreviousKabataan extends Model
{
    protected $table = 'previous_kabataan';

    protected $fillable = [
        'kabataan_registration_id',
        'tenant_id',
        'barangay_id',
        'moved_by_user_id',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'email',
        'contact_number',
        'form_data',
        'profiling_year',
        'moved_at',
    ];

    protected $casts = [
        'form_data' => 'array',
        'moved_at'  => 'datetime',
    ];

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(KabataanRegistration::class, 'kabataan_registration_id');
    }

    public function scopeForBarangay($query, int $barangayId)
    {
        return $query->where('barangay_id', $barangayId);
    }

    public function getFullNameAttribute(): string
    {
        $name = trim($this->first_name . ' ' . $this->middle_name);
        return implode(', ', array_filter([$this->last_name, $name])) . ($this->suffix ? ', ' . $this->suffix : '');
    }
}
