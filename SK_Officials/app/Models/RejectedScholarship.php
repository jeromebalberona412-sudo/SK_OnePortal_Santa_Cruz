<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RejectedScholarship extends Model
{
    protected $table = 'rejected_scholarships';

    protected $fillable = [
        'program_application_id',
        'tenant_id',
        'barangay_id',
        'rejected_by_user_id',
        'rejection_reason',
        'rejection_reasons',
        'rejected_at',
        'restored_at',
    ];

    protected function casts(): array
    {
        return [
            'rejection_reasons' => 'array',
            'rejected_at' => 'datetime',
            'restored_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(ProgramApplication::class, 'program_application_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('restored_at');
    }

    public function scopeForBarangay($query, int $barangayId)
    {
        return $query->where('barangay_id', $barangayId);
    }
}
