<?php

namespace App\Modules\Accounts\Requests;

use App\Modules\Accounts\Models\OfficialProfile;
use App\Modules\Shared\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSkFed() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $suffix = $this->input('suffix');

        if ($suffix === '' || $suffix === 'None') {
            $this->merge(['suffix' => null]);
        }

        if ($this->filled('email')) {
            $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
        }
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        $accountRole = (string) ($this->route('user')?->role ?? '');
        $requiresDemographics = in_array($accountRole, [
            User::ROLE_SK_FED,
            User::ROLE_SK_OFFICIAL,
        ], true);

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'suffix' => ['nullable', Rule::in(['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'])],
            'sex' => [$requiresDemographics ? 'required' : 'nullable', Rule::in(['Male', 'Female'])],
            'date_of_birth' => [$requiresDemographics ? 'required' : 'nullable', 'date', 'before:today'],
            'age' => ['nullable', 'integer', 'min:0', 'max:150'],
            'contact_number' => [$requiresDemographics ? 'required' : 'nullable', 'string', 'max:20'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)->whereNull('deleted_at'),
            ],
            'status' => ['required', Rule::in([
                User::STATUS_ACTIVE,
                User::STATUS_INACTIVE,
                User::STATUS_PENDING_APPROVAL,
                User::STATUS_SUSPENDED,
            ])],
            'barangay_id' => ['required', 'integer', 'exists:barangays,id'],
            'position' => [
                $accountRole === User::ROLE_SK_FED ? 'required' : 'nullable',
                Rule::in(OfficialProfile::positionsForRole($accountRole !== '' ? $accountRole : User::ROLE_SK_FED)),
            ],
            'federation_position' => ['nullable', Rule::in(OfficialProfile::FEDERATION_POSITIONS)],
            'term_start' => ['required', 'date', 'after_or_equal:2023-01-01'],
            'term_end' => ['required', 'date', 'after:term_start'],
            'term_status' => ['required', Rule::in(['ACTIVE', 'INACTIVE', 'EXPIRED', 'REPLACED'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already taken.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
        ];
    }
}
