<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleProgram extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'tenant_id',
        'barangay_id',
        'created_by',
        'program_type',
        'committee',
        'program_name',
        'program_letter',
        'participation_quantity',
        'start_date',
        'end_date',
        'status',
        'is_archived',
        'archived_at',
        'archived_by',
        'deleted_reason',
        'restored_at',
        'restored_by',
        'announcement',
        'scholarship_details',
        'kk_profiling_fields',
        'custom_questions',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'participation_quantity' => 'integer',
            'is_archived' => 'boolean',
            'archived_at' => 'datetime',
            'restored_at' => 'datetime',
            'scholarship_details' => 'array',
            'kk_profiling_fields' => 'array',
            'custom_questions' => 'array',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where(function ($builder) {
            $builder->whereRaw('"is_archived" = false')
                ->orWhereNull('is_archived');
        });
    }

    public function scopeArchived($query)
    {
        return $query->whereRaw('"is_archived" = true');
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
