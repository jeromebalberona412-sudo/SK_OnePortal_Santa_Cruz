<?php

namespace App\Modules\Barangay_ABYIP\Services\Category;

class ExpenditureClassifier
{
    public function looksLikeNewPpaStart(string $line): bool
    {
        $line = trim($line);
        if ($line === '' || mb_strlen($line) < 3) {
            return false;
        }

        if ($this->looksLikePersonFragment($line)) {
            return false;
        }

        if (preg_match('/^([A-J])\.\s/i', $line) === 1) {
            return false;
        }

        if (preg_match('/^(Code|PPAs?|Description|Expected|Performance|Period|Budget|Person|MOOE|CO|Total)\b/i', $line) === 1) {
            return false;
        }

        if (preg_match('/^[\d,₱.\s]+$/', $line) === 1) {
            return false;
        }

        return preg_match('/^[A-Za-z]/', $line) === 1;
    }

    public function looksLikePersonFragment(string $line): bool
    {
        return preg_match('/^(Sangguniang|Kabataan|Council|SK\s|SKTreasurer|SKChairman|Treasurer|Chairman|Chairperson|BADAC|ALS)/i', $line) === 1;
    }

    public function looksLikeSectionHeading(string $line): bool
    {
        return preg_match('/^(I{1,3}|IV|V)\.\s*(Receipts|Expenditure)/i', $line) === 1
            || preg_match('/SK\s+YOUTH\s+DEVELOPMENT/i', $line) === 1
            || preg_match('/^TOTAL\b/i', $line) === 1;
    }
}
