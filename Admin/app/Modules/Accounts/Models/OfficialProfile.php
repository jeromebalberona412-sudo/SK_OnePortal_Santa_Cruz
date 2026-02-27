<?php

namespace App\Modules\Accounts\Models;

use App\Modules\Shared\Models\Tenant;
use App\Modules\Shared\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OfficialProfile extends Model
{
    use HasFactory;

    public const POSITION_CHAIRMAN = 'Chairman';
    public const POSITION_COUNCILOR = 'Councilor';
    public const POSITION_KAGAWAD = 'Kagawad';
    public const POSITION_TREASURER = 'Treasurer';
    public const POSITION_SECRETARY = 'Secretary';
    public const POSITION_AUDITOR = 'Auditor';
    public const POSITION_PIO = 'PIO';

    public const POSITIONS = [
        self::POSITION_CHAIRMAN,
        self::POSITION_COUNCILOR,
        self::POSITION_KAGAWAD,
        self::POSITION_TREASURER,
        self::POSITION_SECRETARY,
        self::POSITION_AUDITOR,
        self::POSITION_PIO,
    ];

    protected $fillable = [
        'tenant_id',
        'user_id',
        'first_name',
        'last_name',
        'middle_initial',
        'suffix',
        'position',
        'municipality',
        'province',
        'region',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
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
