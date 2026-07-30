<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramAccomplishmentReport extends Model
{
    protected $table = 'programs_accomplishment_reports';

    protected $fillable = [
        'tenant_id',
        'barangay_id',
        'program_id',
        'created_by',
        'title',
        'description',
        'objectives',
        'implementation_summary',
        'lessons_learned',
        'recommendations',
        'venue',
        'person_responsible',
        'date_started',
        'date_completed',
        'participants_count',
        'budget_allocated',
        'actual_expense',
        'accomplishment_status',
        'remarks',
        'image_name',
        'image_path',
        'image_type',
        'image_size',
        'image_caption',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'submitted_at',
        'approved_at',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'date_started' => 'date',
            'date_completed' => 'date',
            'participants_count' => 'integer',
            'budget_allocated' => 'decimal:2',
            'actual_expense' => 'decimal:2',
            'remaining_budget' => 'decimal:2',
            'budget_utilization_percent' => 'decimal:2',
            'image_size' => 'integer',
            'file_size' => 'integer',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Abyip::class, 'program_id');
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForBarangay($query, int $barangayId)
    {
        return $query->where('barangay_id', $barangayId);
    }

    public function scopePublished($query)
    {
        return $query->where('accomplishment_status', 'Published');
    }

    public function scopeApproved($query)
    {
        return $query->where('accomplishment_status', 'Approved');
    }

    public function scopeDraft($query)
    {
        return $query->where('accomplishment_status', 'Draft');
    }

    public function isEditable(): bool
    {
        return $this->accomplishment_status === 'Draft';
    }

    public function isSubmittable(): bool
    {
        return $this->accomplishment_status === 'Draft';
    }

    public function isApproved(): bool
    {
        return $this->accomplishment_status === 'Approved' || $this->accomplishment_status === 'Published';
    }
}
