<?php

namespace App\Modules\Authentication\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $password = (string) $value;

        if (strlen($password) < 12) {
            $fail('The :attribute must be at least 12 characters.');

            return;
        }

        if (! preg_match('/[A-Z]/', $password)) {
            $fail('The :attribute must contain at least one uppercase letter.');

            return;
        }

        if (! preg_match('/[a-z]/', $password)) {
            $fail('The :attribute must contain at least one lowercase letter.');

            return;
        }

        if (! preg_match('/\d/', $password)) {
            $fail('The :attribute must contain at least one number.');

            return;
        }

        if (! preg_match('/[^A-Za-z0-9]/', $password)) {
            $fail('The :attribute must contain at least one special character.');
        }
    }
}
