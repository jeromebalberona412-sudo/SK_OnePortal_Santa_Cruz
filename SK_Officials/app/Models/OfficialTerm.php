<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficialTerm extends Model
{
    public const STATUS_ACTIVE = 'ACTIVE';

    protected $fillable = [
        'official_profile_id',
        'term_start',
        'term_end',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'term_start' => 'date',
            'term_end' => 'date',
        ];
    }

    public function officialProfile(): BelongsTo
    {
        return $this->belongsTo(OfficialProfile::class);
    }
}
