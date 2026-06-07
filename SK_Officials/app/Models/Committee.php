<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Committee extends Model
{
    protected $fillable = [
        'committee_name',
        'committee_head_id',
        'description',
    ];

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'committee_head_id');
    }
}
