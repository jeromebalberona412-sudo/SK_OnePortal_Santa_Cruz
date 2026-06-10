<?php

namespace App\Modules\Authentication\Support;

use Illuminate\Support\Facades\Hash;

class PasswordHelper
{
    public static function matches(string $plainPassword, ?string $storedHash): bool
    {
        if ($storedHash === null || $storedHash === '' || ! self::isBcryptHash($storedHash)) {
            return false;
        }

        try {
            return Hash::check($plainPassword, $storedHash);
        } catch (\RuntimeException) {
            return false;
        }
    }

    public static function isBcryptHash(string $hash): bool
    {
        $info = password_get_info($hash);

        return ($info['algo'] ?? null) !== null;
    }
}
