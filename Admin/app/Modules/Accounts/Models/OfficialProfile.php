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

    public const FEDERATION_POSITIONS = [
        'President',
        'Vice President',
        'Secretary',
        'Treasurer',
        'PIO',
        'Sergeant at Arms',
    ];

    public const OFFICIAL_POSITIONS = [
        'Chairperson',
        'Secretary',
        'Treasurer',
        'Kagawad',
    ];

    /** @deprecated Use federationPositionOptions() or officialPositionOptions() */
    public const POSITIONS = [
        ...self::FEDERATION_POSITIONS,
        ...self::OFFICIAL_POSITIONS,
        'Chairman',
        'Councilor',
        'Auditor',
    ];

    public static function federationPositionOptions(): array
    {
        return [
            'President' => 'President',
            'Vice President' => 'Vice President',
            'Secretary' => 'Secretary',
            'Treasurer' => 'Treasurer',
            'PIO' => 'PIO',
            'Sergeant at Arms' => 'Sergeant at Arms',
        ];
    }

    public static function officialPositionOptions(): array
    {
        return [
            'Chairperson' => 'SK Chairperson',
            'Secretary' => 'SK Secretary',
            'Treasurer' => 'SK Treasurer',
            'Kagawad' => 'SK Kagawad',
        ];
    }

    public static function positionsForRole(string $role): array
    {
        return $role === \App\Modules\Shared\Models\User::ROLE_SK_FED
            ? self::FEDERATION_POSITIONS
            : array_merge(self::OFFICIAL_POSITIONS, ['Chairman', 'Councilor', 'Auditor', 'PIO']);
    }

    /**
     * @return array<int, string>
     */
    public static function allowedPositions(): array
    {
        return array_values(array_unique([
            ...self::FEDERATION_POSITIONS,
            ...self::OFFICIAL_POSITIONS,
            'Chairman',
            'Councilor',
            'Auditor',
        ]));
    }

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

    protected $casts = [
        'date_of_birth' => 'date',
        'age' => 'integer',
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
