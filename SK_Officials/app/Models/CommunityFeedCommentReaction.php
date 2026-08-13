<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityFeedCommentReaction extends Model
{
    protected $table = 'community_feed_comment_reactions';

    protected $fillable = [
        'comment_id',
        'user_id',
        'user_type',
        'reaction_type',
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(CommunityFeedComment::class, 'comment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
