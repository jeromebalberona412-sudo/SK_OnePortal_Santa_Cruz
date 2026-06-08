<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbyipProgram extends Model
{
    protected $table = 'abyip_programs';

    protected $fillable = [
        'abyip_id',
        'code',
        'program_name',
        'description',
        'expected_result',
        'performance_indicator',
        'implementation_period',
        'person_responsible',
        'row_type',
        'sort_order',
    ];

    public function getProgramLetterAttribute(): ?string
    {
        $code = strtoupper(trim((string) $this->code));

        return preg_match('/^[A-J]$/', $code) === 1 ? $code : null;
    }

    public function abyip(): BelongsTo
    {
        return $this->belongsTo(AbyipDocument::class, 'abyip_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(AbyipProgramActivity::class, 'program_id');
    }
}
