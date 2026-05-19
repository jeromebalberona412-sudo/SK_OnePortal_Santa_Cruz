<?php

namespace App\Modules\Manage_Kabataan\Models;

use App\Modules\Accounts\Models\Barangay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kabataan extends Model
{
    protected $table = 'kabataan';

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'suffix',
        'kk_number',
        'age',
        'gender',
        'birthday',
        'barangay_id',
        'purok_zone',
        'contact_number',
        'email',
        'youth_classification',
        'educational_background',
        'work_status',
        'civil_status',
        'sk_voter',
        'national_voter',
        'kk_assembly_attendance',
        'account_status',
        'verification_status',
    ];

    protected $casts = [
        'birthday' => 'date',
    ];

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'barangay_id');
    }
}
