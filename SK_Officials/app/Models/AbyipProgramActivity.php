<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbyipProgramActivity extends Model
{
    protected $table = 'abyip_program_activities';

    protected $fillable = [
        'abyip_id',
        'program_id',
        'activity_name',
        'code',
        'ppas',
        'description',
        'expected_result',
        'performance_indicator',
        'period_of_implementation',
        'budget',
        'person_responsible',
        'mooe',
        'co',
        'total',
        'row_type',
        'program_section',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'mooe' => 'decimal:2',
            'co' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function abyip(): BelongsTo
    {
        return $this->belongsTo(AbyipDocument::class, 'abyip_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(AbyipProgram::class, 'program_id');
    }
}
