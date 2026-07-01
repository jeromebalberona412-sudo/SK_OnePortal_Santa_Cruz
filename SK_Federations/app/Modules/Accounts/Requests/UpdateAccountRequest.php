<?php

namespace App\Modules\Accounts\Requests;

use App\Modules\Accounts\Models\OfficialProfile;
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
            'term_start' => ['required', 'date', 'after_or_equal:2023-01-01', 'before_or_equal:'.Carbon::now()->toDateString()],
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

            try {
                $start = Carbon::parse($termStart)->startOfDay();
                $end = Carbon::parse($termEnd)->startOfDay();
            } catch (\Throwable) {
                return;
            }

            $requiredEndYear = $start->year + 4;
            if ($end->year !== $requiredEndYear) {
                $validator->errors()->add(
                    'term_end',
                    'Term end year must be exactly 4 years after the term start year.'
                );

                return;
            }

            $endYearStart = Carbon::create($requiredEndYear, 1, 1)->startOfDay();
            $endYearEnd = Carbon::create($requiredEndYear, 12, 31)->startOfDay();

            if ($end->lt($endYearStart) || $end->gt($endYearEnd)) {
                $validator->errors()->add(
                    'term_end',
                    'Term end date must fall within the term end year.'
                );
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
        ];
    }
}
