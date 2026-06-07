<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AbyipProgram extends Model
{
    public $incrementing = false;

    protected $table = 'abyip_programs';

    protected $keyType = 'string';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'programs',
    ];

    public function abyips(): BelongsToMany
    {
        return $this->belongsToMany(
            AbyipDocument::class,
            'abyip_detected_programs',
            'program_id',
            'abyip_id'
        );
    }
}
