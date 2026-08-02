<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'participants_count',
        'actual_expense',
        'remarks',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'participants_count' => 'integer',
            'actual_expense' => 'decimal:2',
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
        return $this->status === 'Draft' || $this->status === 'Unpublished';
    }

    public function isPublished(): bool
    {
        return $this->status === 'Published';
    }

    public function getRemainingBudgetAttribute(): float
    {
        if (!$this->program) {
            return 0.0;
        }

        return ($this->program->budget_allocated ?? 0) - $this->actual_expense;
    }

    public function getBudgetUtilizationPercentAttribute(): float
    {
        if (!$this->program || !$this->program->budget_allocated) {
            return 0.0;
        }

        return ($this->actual_expense / $this->program->budget_allocated) * 100;
    }
}
