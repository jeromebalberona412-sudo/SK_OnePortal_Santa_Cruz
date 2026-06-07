<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OfficialProfile extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'first_name',
        'last_name',
        'middle_name',
        'suffix',
        'sex',
        'date_of_birth',
        'age',
        'contact_number',
        'position',
        'municipality',
        'province',
        'region',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'age' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function terms(): HasMany
    {
        return $this->hasMany(OfficialTerm::class);
    }

    public function latestTerm(): HasOne
    {
        return $this->hasOne(OfficialTerm::class)->latestOfMany('term_end');
    }
}
