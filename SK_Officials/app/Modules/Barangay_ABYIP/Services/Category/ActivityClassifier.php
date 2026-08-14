<?php

namespace App\Modules\Barangay_ABYIP\Services\Category;

class ActivityClassifier
{
    public function normalizeName(string $name): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);
        $name = preg_replace('/^[\x{2022}\x{25CF}\x{F0B7}\x{2013}\x{2023}\x{00B7}•\-]\s*/u', '', $name) ?? $name;
        $name = preg_replace('/^[A-J]\.\s*/i', '', $name) ?? $name;
        $name = preg_replace('/([a-z])([A-Z])/', '$1 $2', $name) ?? $name;
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;

        return trim($name);
    }

    public function isBulletLine(string $line): bool
    {
        return preg_match('/^[\x{2022}\x{25CF}\x{F0B7}\x{2013}\x{2023}\x{00B7}•\-]\s*.+/u', $line) === 1;
    }
}
