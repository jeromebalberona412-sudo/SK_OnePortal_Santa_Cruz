<?php

namespace App\Modules\Manage_Location\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sitio extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'barangay_id',
        'name',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the barangay that owns this sitio
     */
    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }
}
