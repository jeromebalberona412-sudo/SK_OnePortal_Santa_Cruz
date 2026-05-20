<?php

namespace App\Modules\Manage_Location\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barangay extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'municipality',
        'province',
        'region',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get all puroks for this barangay
     */
    public function puroks(): HasMany
    {
        return $this->hasMany(Purok::class);
    }

    /**
     * Get all sitios for this barangay
     */
    public function sitios(): HasMany
    {
        return $this->hasMany(Sitio::class);
    }

    /**
     * Get total purok count
     */
    public function getTotalPurokAttribute(): int
    {
        return $this->puroks()->count();
    }

    /**
     * Get total sitio count
     */
    public function getTotalSitioAttribute(): int
    {
        return $this->sitios()->count();
    }
}
