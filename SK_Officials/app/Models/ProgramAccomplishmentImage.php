<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramAccomplishmentImage extends Model
{
    protected $table = 'programs_accomplishment_report_images';

    protected $fillable = [
        'accomplishment_report_id',
        'cloudinary_public_id',
        'image_url',
        'secure_url',
        'display_name',
        'caption',
        'sort_order',
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
}