<?php

namespace App\Modules\Barangay_ABYIP\Services\Category;

class YouthProgramClassifier
{
    public function letterFromLabel(string $label): ?string
    {
        if (preg_match('/^\s*([A-J])\.(?:\s+|$|[A-Za-z])/i', trim($label), $matches)) {
            return strtoupper($matches[1]);
        }

        return null;
    }

    public function isLetterHeading(string $line): bool
    {
        return $this->letterFromLabel($line) !== null;
    }

    public function isValidLetter(string $letter): bool
    {
        return preg_match('/^[A-J]$/', strtoupper($letter)) === 1;
    }
}
