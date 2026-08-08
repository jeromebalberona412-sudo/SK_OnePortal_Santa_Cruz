<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramAccomplishmentImage extends Model
{
    public const STATUS_PUBLISHED = 'Published';

    public const STATUS_UNPUBLISHED = 'Unpublished';

    public const STATUS_ARCHIVED = 'Archived';

    protected $table = 'programs_accomplishment';

    protected $fillable = [
        'accomplishment_report_id',
        'cloudinary_public_id',
        'image_url',
        'secure_url',
        'display_name',
        'caption',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function accomplishmentReport(): BelongsTo
    {
        return $this->belongsTo(ProgramAccomplishmentReport::class, 'accomplishment_report_id');
    }

    public function scopeForReport($query, int $reportId)
    {
        return $query->where('accomplishment_report_id', $reportId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }
}