<?php

namespace App\Modules\Reports_Management\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Placeholder request for future report upload validation.
 */
class StoreReportUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
