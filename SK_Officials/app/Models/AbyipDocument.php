<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AbyipDocument extends Model
{
    public const SOURCE_WORD = 'word';

    public const SOURCE_PDF = 'pdf';

    protected $table = 'abyips';

    protected $fillable = [
        'tenant_id',
        'barangay_id',
        'created_by',
        'title',
        'calendar_year',
        'region',
        'province',
        'municipality',
        'sk_council_name',
        'barangay_estimated_budget',
        'sk_fund_amount',
        'total_expenditure',
        'prepared_by_name',
        'prepared_by_position',
        'approved_by_name',
        'approved_by_position',
        'source_type',
        'document_html',
        'pdf_data',
        'sk_youth_development_and_empowerment_programs',
    ];

    protected function casts(): array
    {
        return [
            'calendar_year' => 'integer',
            'barangay_estimated_budget' => 'decimal:2',
            'sk_fund_amount' => 'decimal:2',
            'total_expenditure' => 'decimal:2',
            'sk_youth_development_and_empowerment_programs' => 'array',
        ];
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function detectedPrograms(): BelongsToMany
    {
        return $this->belongsToMany(
            AbyipProgram::class,
            'abyip_detected_programs',
            'abyip_id',
            'program_id'
        );
    }
}
