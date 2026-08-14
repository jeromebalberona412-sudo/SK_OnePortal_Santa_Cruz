<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramAccomplishmentReport extends Model
{
    public const STATUS_DRAFT = 'Draft';

    public const STATUS_SUBMITTED = 'Submitted';

    public const STATUS_UNPUBLISHED = 'Unpublished';

    public const STATUS_REJECTED = 'Rejected';

    public const STATUS_PUBLISHED = 'Published';

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
        'actual_result',
        'lessons_learned',
        'recommendations',
        'participants_count',
        'target_beneficiaries',
        'actual_expense',
        'approved_budget',
        'actual_implementation_date',
        'actual_completion_date',
        'remarks',
        'status',
        'submitted_at',
        'published_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'participants_count' => 'integer',
            'target_beneficiaries' => 'integer',
            'actual_expense' => 'decimal:2',
            'approved_budget' => 'decimal:2',
            'actual_implementation_date' => 'date',
            'actual_completion_date' => 'date',
            'submitted_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(ScheduleProgram::class, 'program_id');
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProgramAccomplishmentImage::class, 'accomplishment_report_id')->ordered();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProgramAccomplishmentDocument::class, 'accomplishment_report_id');
    }

    public function scopeForBarangay($query, int $barangayId)
    {
        return $query->where('barangay_id', $barangayId);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'Published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'Draft');
    }

    public function scopeUnpublished($query)
    {
        return $query->where('status', 'Unpublished');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_SUBMITTED,
            self::STATUS_UNPUBLISHED,
            self::STATUS_REJECTED,
        ], true);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function canPublish(): bool
    {
        return in_array($this->status, [
            self::STATUS_SUBMITTED,
            self::STATUS_UNPUBLISHED,
        ], true);
    }

    public function plannedBudget(): float
    {
        if ($this->approved_budget !== null) {
            return (float) $this->approved_budget;
        }

        return (float) ($this->program?->participation_quantity ?? 0);
    }

    public function getRemainingBudgetAttribute(): float
    {
        return max(0, $this->plannedBudget() - (float) $this->actual_expense);
    }

    public function getBudgetUtilizationPercentAttribute(): float
    {
        $budget = $this->plannedBudget();

        if ($budget <= 0) {
            return 0.0;
        }

        return ((float) $this->actual_expense / $budget) * 100;
    }
}
