<?php

namespace App\Modules\Shared\Models;

use App\Modules\Profile\Models\Barangay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    protected $fillable = [
        'user_id', 'barangay_id', 'type', 'title', 'body',
        'image_url', 'link_url', 'is_federation_wide',
    ];

    protected $casts = [
        'is_federation_wide' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Profile\Models\Barangay::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(AnnouncementReaction::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(AnnouncementComment::class)->orderBy('created_at');
    }
}
