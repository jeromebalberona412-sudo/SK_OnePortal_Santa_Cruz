<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KKProfilingSchedule extends Model
{
    protected $table = 'kk_profiling_schedules';

    protected $fillable = [
        'tenant_id',
        'barangay_id',
        'created_by',
        'date_start',
        'date_expiry',
        'link',
        'status',
    ];

    protected $casts = [
        'date_start'  => 'date:Y-m-d',
        'date_expiry' => 'date:Y-m-d',
    ];

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    /**
     * Scope: schedules that currently open signup for their barangay.
     * Open = status is Upcoming or Ongoing AND today is within the date range.
     */
    public function scopeActive($query)
    {
        $today = now()->toDateString();
        return $query->whereIn('status', ['Upcoming', 'Ongoing'])
                     ->where('date_start', '<=', $today)
                     ->where('date_expiry', '>=', $today);
    }
}
