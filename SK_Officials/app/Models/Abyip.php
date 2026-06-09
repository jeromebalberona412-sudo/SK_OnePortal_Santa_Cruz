<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Abyip extends Model
{
    public const ROW_DOCUMENT = 'document';

    public const ROW_EXPENDITURE = 'expenditure';

    public const ROW_YOUTH_PROGRAM = 'youth_program';

    public const ROW_ACTIVITY = 'activity';

    public const SOURCE_WORD = 'word';

    public const SOURCE_PDF = 'pdf';

    protected $table = 'abyip';

    protected $fillable = [
        'document_id',
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
        'row_type',
        'parent_id',
        'code',
        'program_name',
        'description',
        'expected_result',
        'performance_indicator',
        'implementation_period',
        'person_responsible',
        'mooe',
        'co',
        'total',
        'budget',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'fiscal_year' => 'integer',
            'barangay_estimated_budget' => 'decimal:2',
            'sk_fund_percentage' => 'decimal:2',
            'sk_fund_amount' => 'decimal:2',
            'total_budget' => 'decimal:2',
            'mooe' => 'decimal:2',
            'co' => 'decimal:2',
            'total' => 'decimal:2',
            'budget' => 'decimal:2',
        ];
    }

    public function scopeDocuments(Builder $query): Builder
    {
        return $query->where('row_type', self::ROW_DOCUMENT);
    }

    public function scopeLines(Builder $query): Builder
    {
        return $query->where('row_type', '!=', self::ROW_DOCUMENT);
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

    public function getProgramLetterAttribute(): ?string
    {
        $code = strtoupper(trim((string) $this->code));

        return preg_match('/^[A-J]$/', $code) === 1 ? $code : null;
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

    public function document(): BelongsTo
    {
        return $this->belongsTo(self::class, 'document_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(self::class, 'document_id')
            ->lines()
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('row_type', self::ROW_ACTIVITY)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function activities(): HasMany
    {
        return $this->children();
    }

    public function isDocument(): bool
    {
        return $this->row_type === self::ROW_DOCUMENT;
    }
}
