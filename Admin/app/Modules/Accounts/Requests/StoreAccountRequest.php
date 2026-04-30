<?php

namespace App\Modules\Accounts\Requests;

use App\Modules\Accounts\Models\OfficialProfile;
use App\Modules\Shared\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $suffix = $this->input('suffix');

        if ($suffix === '' || $suffix === 'None') {
            $this->merge(['suffix' => null]);
        }
    }

    public function rules(): array
    {
        $requiresDemographics = in_array($this->input('role'), [
            User::ROLE_SK_FED,
            User::ROLE_SK_OFFICIAL,
        ], true);

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'suffix' => ['nullable', Rule::in(['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'])],
            'sex' => [$requiresDemographics ? 'required' : 'nullable', Rule::in(['Male', 'Female']), 'not_in:'],
            'date_of_birth' => [$requiresDemographics ? 'required' : 'nullable', 'date', 'before:today'],
            'age' => ['nullable', 'integer', 'min:0', 'max:150'],
            'contact_number' => [$requiresDemographics ? 'required' : 'nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in([
                User::ROLE_SK_FED,
                User::ROLE_SK_OFFICIAL,
            ]), 'not_in:'],
            'status' => ['required', Rule::in([
                User::STATUS_ACTIVE,
                User::STATUS_INACTIVE,
                User::STATUS_PENDING_APPROVAL,
                User::STATUS_SUSPENDED,
            ]), 'not_in:'],
            'barangay_id' => ['required', 'integer', 'exists:barangays,id', 'not_in:'],
            'position' => ['required', Rule::in(OfficialProfile::POSITIONS), 'not_in:'],
            'term_start' => ['required', 'date'],
            'term_end' => ['required', 'date', 'after:term_start'],
            'term_status' => ['required', Rule::in(['ACTIVE', 'INACTIVE', 'EXPIRED', 'REPLACED'])],
        ];
    }
}
