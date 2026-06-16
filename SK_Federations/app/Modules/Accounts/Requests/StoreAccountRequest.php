<?php

namespace App\Modules\Accounts\Requests;

use App\Modules\Accounts\Models\OfficialProfile;
use App\Modules\Shared\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAccountRequest extends FormRequest
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

        foreach (['first_name', 'last_name', 'middle_name'] as $field) {
            if ($this->filled($field)) {
                $this->merge([$field => mb_strtoupper(trim((string) $this->input($field)), 'UTF-8')]);
            }
        }

        if ($this->filled('contact_number')) {
            $digits = preg_replace('/\D+/', '', (string) $this->input('contact_number')) ?? '';
            if (! str_starts_with($digits, '09')) {
                $digits = '09'.ltrim($digits, '0');
            }

            $this->merge(['contact_number' => substr($digits, 0, 11)]);
        }

        if (! $this->filled('status')) {
            $this->merge(['status' => User::STATUS_ACTIVE]);
        }
    }

    public function rules(): array
    {
        $requiresDemographics = in_array($this->input('role'), [
            User::ROLE_SK_FED,
            User::ROLE_SK_OFFICIAL,
        ], true);

        $currentYearStart = now()->startOfYear()->toDateString();

        return [
            'first_name' => ['required', 'string', 'max:100', 'regex:/^[A-Z\s\-\']+$/u'],
            'last_name' => ['required', 'string', 'max:100', 'regex:/^[A-Z\s\-\']+$/u'],
            'middle_name' => ['nullable', 'string', 'max:100', 'regex:/^[A-Z\s\-\']*$/u'],
            'suffix' => ['nullable', Rule::in(['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'])],
            'sex' => [$requiresDemographics ? 'required' : 'nullable', Rule::in(['Male', 'Female']), 'not_in:'],
            'date_of_birth' => [$requiresDemographics ? 'required' : 'nullable', 'date', 'before:today'],
            'age' => ['nullable', 'integer', 'min:0', 'max:150'],
            'contact_number' => [$requiresDemographics ? 'required' : 'nullable', 'regex:/^09\d{9}$/'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
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
            'position' => ['required', Rule::in(OfficialProfile::positionsForRole((string) $this->input('role'))), 'not_in:'],
            'term_start' => ['required', 'date', 'after_or_equal:'.$currentYearStart],
            'term_end' => ['required', 'date', 'after:term_start'],
            'term_status' => ['required', Rule::in(['ACTIVE', 'INACTIVE', 'EXPIRED', 'REPLACED'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
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

            if ($end->gt($start->copy()->addYears(5))) {
                $validator->errors()->add(
                    'term_end',
                    'Term end date must be within 5 years of the term start date.'
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
            'first_name.regex' => 'First name must use uppercase letters only.',
            'last_name.regex' => 'Last name must use uppercase letters only.',
            'middle_name.regex' => 'Middle name must use uppercase letters only.',
            'contact_number.regex' => 'Contact number must be 11 digits starting with 09.',
            'term_start.after_or_equal' => 'Term start date cannot be before the current year.',
            'term_end.after' => 'Term end date must be after the term start date.',
        ];
    }
}
