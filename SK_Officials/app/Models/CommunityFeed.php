<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityFeed extends Model
{
    protected $table = 'community_feeds';

    protected $fillable = [
        'user_id',
        'barangay_id',
        'type',
        'title',
        'body',
        'link_url',
        'is_federation_wide',
        'is_archived',
        'archived_at',
        'deleted_at',
    ];

    protected $casts = [
        'is_federation_wide' => 'boolean',
        'is_archived'        => 'boolean',
        'archived_at'        => 'datetime',
        'deleted_at'         => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CommunityFeedComment::class, 'community_feed_id')->orderBy('created_at');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(CommunityFeedReaction::class, 'community_feed_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(CommunityFeedImage::class, 'community_feed_id')->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereRaw('"is_archived" = false');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereRaw('"is_archived" = true');
    }
}
