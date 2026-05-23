<?php

namespace App\Modules\Reports\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ReportDocument extends Model
{
    protected $fillable = [
        'title',
        'filename',
        'file_path',
        'content',
        'uploaded_by',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function hasStoredFile(): bool
    {
        return $this->file_path && Storage::disk('local')->exists($this->file_path);
    }

    public function fullStoragePath(): ?string
    {
        if (! $this->hasStoredFile()) {
            return null;
        }

        return Storage::disk('local')->path($this->file_path);
    }
}
