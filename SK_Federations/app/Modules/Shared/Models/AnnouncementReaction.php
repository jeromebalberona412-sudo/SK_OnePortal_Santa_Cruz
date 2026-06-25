<?php

namespace App\Modules\Shared\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementReaction extends Model
{
    protected $fillable = ['announcement_id', 'user_id', 'user_type'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
