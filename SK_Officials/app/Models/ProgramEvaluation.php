<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramEvaluation extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'tenant_id',
        'barangay_id',
        'created_by',
        'program_letter',
        'schedule_program_id',
        'title',
        'instructions',
        'custom_questions',
        'status',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'custom_questions' => 'array',
            'due_date' => 'date',
        ];
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scheduleProgram(): BelongsTo
    {
        return $this->belongsTo(ScheduleProgram::class);
    }
}
