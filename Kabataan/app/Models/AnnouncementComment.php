<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementComment extends Model
{
    protected $fillable = ['announcement_id', 'parent_id', 'user_id', 'user_type', 'author_name', 'body'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
