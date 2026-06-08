<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbyipDocument extends Model
{
    public const SOURCE_WORD = 'word';

    public const SOURCE_PDF = 'pdf';

    protected $table = 'abyips';

    protected $fillable = [
        'tenant_id',
        'barangay_id',
        'created_by',
        'fiscal_year',
        'country',
        'region',
        'province',
        'municipality',
        'barangay_name',
        'document_title',
        'sk_council_name',
        'barangay_estimated_budget',
        'sk_fund_percentage',
        'sk_fund_amount',
        'total_budget',
        'prepared_by',
        'prepared_position',
        'prepared_by_user_id',
        'approved_by',
        'approved_position',
        'approved_by_user_id',
        'source_type',
        'document_html',
        'pdf_data',
    ];

    protected function casts(): array
    {
        return [
            'fiscal_year' => 'integer',
            'barangay_estimated_budget' => 'decimal:2',
            'sk_fund_percentage' => 'decimal:2',
            'sk_fund_amount' => 'decimal:2',
            'total_budget' => 'decimal:2',
        ];
    }

    public function getTitleAttribute(): ?string
    {
        return $this->document_title;
    }

    public function getCalendarYearAttribute(): ?int
    {
        return $this->fiscal_year;
    }

    public function getTotalExpenditureAttribute(): ?string
    {
        return $this->total_budget;
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function preparedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by_user_id');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function programs(): HasMany
    {
        return $this->hasMany(AbyipProgram::class, 'abyip_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(AbyipProgramActivity::class, 'abyip_id');
    }
}
