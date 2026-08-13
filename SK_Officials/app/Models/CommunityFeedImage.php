<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityFeedImage extends Model
{
    public $timestamps = false;

    protected $table = 'community_feed_images';

    protected $fillable = [
        'community_feed_id',
        'image_url',
        'public_id',
        'sort_order',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function communityFeed(): BelongsTo
    {
        return $this->belongsTo(CommunityFeed::class, 'community_feed_id');
    }
}
