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
        'program_letter',
        'program_name',
        'sort_order',
    ];

    public function abyip(): BelongsTo
    {
        return $this->belongsTo(AbyipDocument::class, 'abyip_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(AbyipProgramActivity::class, 'program_id');
    }
}
