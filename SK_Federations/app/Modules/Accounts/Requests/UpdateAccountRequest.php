<?php

namespace App\Modules\Accounts\Requests;

use App\Modules\Accounts\Models\OfficialProfile;
use App\Modules\Accounts\Support\SkOfficialTermDates;
use App\Modules\Shared\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isFederationAdministrator() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $suffix = $this->input('suffix');

        if ($suffix === '__other__') {
            $other = trim((string) $this->input('suffix_other', ''));
            if ($other !== '') {
                $this->merge(['suffix' => mb_strtoupper($other, 'UTF-8')]);
            }
        } elseif ($suffix === '' || $suffix === 'None') {
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
            'suffix' => ['nullable', Rule::in(['Jr.', 'Sr.', 'II', 'III', 'IV', 'V', '__other__'])],
            'suffix_other' => [
                Rule::requiredIf(fn (): bool => $this->input('suffix') === '__other__'),
                'nullable',
                'string',
                'min:1',
                'max:10',
                'regex:/^\S+$/u',
            ],
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
            'term_start' => [
                'required',
                'date',
                'after_or_equal:'.($accountRole === User::ROLE_SK_OFFICIAL
                    ? SkOfficialTermDates::FIRST_START
                    : '2023-01-01'),
                'before_or_equal:'.Carbon::now()->toDateString(),
            ],
            'term_end' => ['required', 'date', 'after:term_start'],
            'term_status' => ['required', Rule::in(['ACTIVE', 'INACTIVE', 'EXPIRED', 'REPLACED'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $accountRole = (string) ($this->route('user')?->role ?? '');
            if ($accountRole !== User::ROLE_SK_OFFICIAL) {
                return;
            }

            $termStart = $this->input('term_start');
            $termEnd = $this->input('term_end');

            if (! is_string($termStart) || ! is_string($termEnd) || $termStart === '' || $termEnd === '') {
                return;
            }

            foreach (SkOfficialTermDates::errorsFor($termStart, $termEnd) as $field => $message) {
                $validator->errors()->add($field, $message);
            }
        });
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
            'term_start.after_or_equal' => (string) ($this->route('user')?->role) === User::ROLE_SK_OFFICIAL
                ? SkOfficialTermDates::startRuleMessage()
                : 'Term start date must be on or after January 1, 2023.',
            'term_start.before_or_equal' => (string) ($this->route('user')?->role) === User::ROLE_SK_OFFICIAL
                ? 'Term start date cannot be in the future. Use the current SK term that began on November 30.'
                : 'Term start date cannot be in the future.',
            'term_end.after' => (string) ($this->route('user')?->role) === User::ROLE_SK_OFFICIAL
                ? SkOfficialTermDates::endRuleMessage()
                : 'Term end date must be after the term start date.',
        ];
    }
}
