<?php

namespace App\Modules\Accounts\Requests;

use App\Modules\Shared\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatchStoreAccountsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in([
                User::ROLE_SK_OFFICIAL,
            ])],
            'accounts' => ['required', 'array', 'min:1', 'max:100'],
            'accounts.*.first_name' => ['nullable', 'string', 'max:100'],
            'accounts.*.middle_name' => ['nullable', 'string', 'max:100'],
            'accounts.*.last_name' => ['nullable', 'string', 'max:100'],
            'accounts.*.suffix' => ['nullable', 'string', 'max:10'],
            'accounts.*.sex' => ['nullable', 'string', 'max:20'],
            'accounts.*.date_of_birth' => ['nullable', 'string', 'max:50'],
            'accounts.*.birthdate' => ['nullable', 'string', 'max:50'],
            'accounts.*.age' => ['nullable'],
            'accounts.*.contact_number' => ['nullable', 'string', 'max:20'],
            'accounts.*.email' => ['nullable', 'string', 'max:255'],
            'accounts.*.position' => ['nullable', 'string', 'max:100'],
            'accounts.*.status' => ['nullable', 'string', 'max:50'],
            'accounts.*.barangay' => ['nullable', 'string', 'max:100'],
            'accounts.*.barangay_name' => ['nullable', 'string', 'max:100'],
            'accounts.*.barangay_id' => ['nullable'],
            'accounts.*.term_start' => ['nullable', 'string', 'max:50'],
            'accounts.*.term_end' => ['nullable', 'string', 'max:50'],
        ];
    }
}
