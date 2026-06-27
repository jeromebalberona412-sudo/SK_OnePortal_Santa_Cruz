<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbyipProgramDuration extends Model
{
    protected $fillable = [
        'barangay_id',
        'abyip_program_id',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function abyipProgram(): BelongsTo
    {
        return $this->belongsTo(Abyip::class, 'abyip_program_id');
    }
}
