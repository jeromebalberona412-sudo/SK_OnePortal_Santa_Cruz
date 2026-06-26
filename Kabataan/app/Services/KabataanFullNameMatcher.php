<?php

namespace App\Services;

class KabataanFullNameMatcher
{
    /**
     * @return array{first: string, middle: string, last: string, suffix: string}
     */
    public function formComponents(string $firstName, ?string $middleName, string $lastName, ?string $suffix = null): array
    {
        return [
            'first' => $this->normalizeToken($firstName),
            'middle' => $this->normalizeToken($middleName ?? ''),
            'last' => $this->normalizeToken($lastName),
            'suffix' => $this->normalizeSuffix($suffix),
        ];
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array{first: string, middle: string, last: string, suffix: string}
     */
    public function formComponentsFromFields(array $fields): array
    {
        $suffix = (string) ($fields['suffix'] ?? '');

        if (strcasecmp($suffix, 'Others') === 0) {
            $suffix = (string) ($fields['custom_suffix'] ?? '');
        }

        if (strcasecmp($suffix, 'None') === 0) {
            $suffix = '';
        }

        return $this->formComponents(
            (string) ($fields['first_name'] ?? ''),
            isset($fields['middle_name']) ? (string) $fields['middle_name'] : null,
            (string) ($fields['last_name'] ?? ''),
            $suffix !== '' ? $suffix : null,
        );
    }

    public function normalizedKeyFromFormFields(array $fields): string
    {
        return $this->normalizedKey($this->formComponentsFromFields($fields));
    }

    public function normalizedKeyFromRegistration(\App\Models\KabataanRegistration $registration): string
    {
        $suffix = $registration->suffix;

        if ($suffix !== null && strcasecmp($suffix, 'None') === 0) {
            $suffix = '';
        }

        return $this->normalizedKey($this->formComponents(
            $registration->first_name,
            $registration->middle_name,
            $registration->last_name,
            $suffix,
        ));
    }

    /**
     * @param  array{first: string, middle: string, last: string, suffix: string}  $form
     * @param  array{first: string, middle: string, last: string, suffix: string}  $ocr
     */
    public function matches(array $form, array $ocr, bool $strictMiddle = false): bool
    {
        if ($this->matchesComponents($form, $ocr, $strictMiddle)) {
            return true;
        }

        $swappedOcr = [
            'first' => $ocr['middle'],
            'middle' => $ocr['first'],
            'last' => $ocr['last'],
            'suffix' => $ocr['suffix'],
        ];

        return $this->matchesComponents($form, $swappedOcr, $strictMiddle);
    }

    /**
     * @param  array{first: string, middle: string, last: string, suffix: string}  $form
     */
    public function matchesFormToOcrText(array $form, string $ocrText, bool $strictMiddle = false): bool
    {
        $ocrText = $this->normalizeToken($ocrText);

        if ($ocrText === '' || $form['first'] === '' || $form['last'] === '') {
            return false;
        }

        if (! str_contains($ocrText, $form['last']) || ! str_contains($ocrText, $form['first'])) {
            return false;
        }

        if ($form['middle'] !== '') {
            if (! $this->ocrTextContainsFormMiddle($form['middle'], $ocrText)) {
                return false;
            }
        } elseif ($strictMiddle) {
            // Form has no middle name — nothing further to verify on the ID.
        }

        if ($form['suffix'] !== '' && ! str_contains($ocrText, $form['suffix'])) {
            return false;
        }

        $parsed = $this->parseOcrName($ocrText, $form);

        if ($parsed !== null) {
            return $this->matchesComponents($form, $parsed, $strictMiddle);
        }

        return true;
    }

    /**
     * @param  array{first: string, middle: string, last: string, suffix: string}  $left
     * @param  array{first: string, middle: string, last: string, suffix: string}  $right
     */
    private function matchesComponents(array $left, array $right, bool $strictMiddle = false): bool
    {
        if ($left['last'] === '' || $left['first'] === '' || $right['last'] === '' || $right['first'] === '') {
            return false;
        }

        if (! $this->tokenMatches($left['last'], $right['last'])) {
            return false;
        }

        if (! $this->tokenMatches($left['first'], $right['first'])) {
            return false;
        }

        if (! $this->middleNamesMatch($left['middle'], $right['middle'], $strictMiddle)) {
            return false;
        }

        if (! $this->suffixesMatch($left['suffix'], $right['suffix'])) {
            return false;
        }

        return true;
    }

    /**
     * @param  array{first: string, middle: string, last: string, suffix: string}  $components
     */
    public function normalizedKey(array $components): string
    {
        $parts = array_filter([
            $components['last'],
            $components['first'],
            $this->middleInitial($components['middle']),
            $components['suffix'],
        ]);

        return implode('|', $parts);
    }

    /**
     * @param  array{first: string, middle: string, last: string, suffix: string}|null  $form
     * @return array{first: string, middle: string, last: string, suffix: string}|null
     */
    public function parseOcrName(string $text, ?array $form = null): ?array
    {
        $text = trim($text);

        if ($text === '') {
            return null;
        }

        if (preg_match('/^([^,]+),\s*(.+)$/i', $text, $commaMatch)) {
            $last = $this->normalizeToken($commaMatch[1]);
            $remainder = trim($commaMatch[2]);
            $remainder = preg_replace('/\b(grade|lrn|section|sy)\b.*$/i', '', $remainder) ?? $remainder;
            $remainder = trim($remainder);

            if ($last !== '' && $remainder !== '') {
                $commaCandidate = $this->parseNameCandidate($remainder.' '.$last, $form);

                if ($commaCandidate !== null) {
                    return $commaCandidate;
                }
            }
        }

        if (preg_match('/\bname\b\s*[:\-]?\s*(.+?)(?:\r?\n|grade|lrn|section|s\.?y\.|signature|$)/i', $text, $match)) {
            $candidate = trim($match[1]);

            if ($parsed = $this->parseNameCandidate($candidate, $form)) {
                return $parsed;
            }
        }

        foreach (preg_split('/\r?\n/', $text) ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || $this->isIgnoredLine($line)) {
                continue;
            }

            if ($parsed = $this->parseNameCandidate($line, $form)) {
                return $parsed;
            }
        }

        return null;
    }

    /**
     * @param  array{first: string, middle: string, last: string, suffix: string}|null  $form
     * @return array{first: string, middle: string, last: string, suffix: string}|null
     */
    private function parseNameCandidate(string $candidate, ?array $form = null): ?array
    {
        $candidate = preg_replace('/\s+/', ' ', trim($candidate)) ?? '';

        if ($candidate === '' || $this->isIgnoredLine($candidate)) {
            return null;
        }

        if (is_array($form) && ($form['last'] ?? '') !== '') {
            $withHints = $this->parseNameCandidateWithFormHints($candidate, $form);

            if ($withHints !== null) {
                return $withHints;
            }
        }

        $suffix = '';
        $suffixPattern = '/\b(JR|SR|II|III|IV|V|VI|VII|VIII|IX|X)\.?$/i';

        if (preg_match($suffixPattern, $candidate, $suffixMatch)) {
            $suffix = $this->normalizeSuffix($suffixMatch[1]);
            $candidate = trim((string) preg_replace($suffixPattern, '', $candidate));
        }

        $tokens = preg_split('/\s+/', $candidate) ?: [];

        if (count($tokens) < 2) {
            return null;
        }

        if (count($tokens) === 2) {
            return [
                'first' => $this->normalizeToken($tokens[0]),
                'middle' => '',
                'last' => $this->normalizeToken($tokens[1]),
                'suffix' => $suffix,
            ];
        }

        $first = $this->normalizeToken(array_shift($tokens));

        if ($tokens === []) {
            return null;
        }

        if (count($tokens) === 1) {
            return [
                'first' => $first,
                'middle' => '',
                'last' => $this->normalizeToken($tokens[0]),
                'suffix' => $suffix,
            ];
        }

        if ($this->isMiddleInitialToken($tokens[0])) {
            $middle = $this->normalizeMiddleInitialToken($tokens[0]);
            array_shift($tokens);

            return [
                'first' => $first,
                'middle' => $middle,
                'last' => $this->normalizeToken(implode(' ', $tokens)),
                'suffix' => $suffix,
            ];
        }

        $last = $this->normalizeToken(array_pop($tokens));
        $middle = $this->normalizeToken(implode(' ', $tokens));

        return [
            'first' => $first,
            'middle' => $middle,
            'last' => $last,
            'suffix' => $suffix,
        ];
    }

    /**
     * @param  array{first: string, middle: string, last: string, suffix: string}  $form
     * @return array{first: string, middle: string, last: string, suffix: string}|null
     */
    private function parseNameCandidateWithFormHints(string $candidate, array $form): ?array
    {
        $suffix = '';
        $suffixPattern = '/\b(JR|SR|II|III|IV|V|VI|VII|VIII|IX|X)\.?$/i';

        if (preg_match($suffixPattern, $candidate, $suffixMatch)) {
            $suffix = $this->normalizeSuffix($suffixMatch[1]);
            $candidate = trim((string) preg_replace($suffixPattern, '', $candidate));
        }

        $tokens = preg_split('/\s+/', $candidate) ?: [];
        $lastTokens = preg_split('/\s+/', $form['last']) ?: [];

        if ($tokens === [] || $lastTokens === []) {
            return null;
        }

        $lastTokenCount = count($lastTokens);

        if (count($tokens) <= $lastTokenCount) {
            return null;
        }

        $tail = array_slice($tokens, -$lastTokenCount);
        $tailNormalized = array_map(fn (string $token) => $this->normalizeToken($token), $tail);

        if ($tailNormalized !== $lastTokens) {
            return null;
        }

        $prefixTokens = array_slice($tokens, 0, -$lastTokenCount);

        if ($prefixTokens === []) {
            return null;
        }

        $first = $this->normalizeToken(array_shift($prefixTokens));

        if ($prefixTokens === []) {
            return [
                'first' => $first,
                'middle' => '',
                'last' => $form['last'],
                'suffix' => $suffix,
            ];
        }

        if (count($prefixTokens) === 1) {
            $middleToken = $prefixTokens[0];

            return [
                'first' => $first,
                'middle' => $this->isMiddleInitialToken($middleToken)
                    ? $this->normalizeMiddleInitialToken($middleToken)
                    : $this->normalizeToken($middleToken),
                'last' => $form['last'],
                'suffix' => $suffix,
            ];
        }

        if ($this->isMiddleInitialToken($prefixTokens[0])) {
            return [
                'first' => $first,
                'middle' => $this->normalizeMiddleInitialToken($prefixTokens[0]),
                'last' => $form['last'],
                'suffix' => $suffix,
            ];
        }

        return [
            'first' => $first,
            'middle' => $this->normalizeToken(implode(' ', $prefixTokens)),
            'last' => $form['last'],
            'suffix' => $suffix,
        ];
    }

    private function isIgnoredLine(string $line): bool
    {
        $normalized = $this->normalizeToken($line);

        if ($normalized === '') {
            return true;
        }

        if ($this->looksLikePersonNameLine($normalized)) {
            return false;
        }

        $blocked = [
            'SCHOOL', 'HIGH', 'MEMORIAL', 'NATIONAL', 'ELEMENTARY', 'ACADEMY', 'COLLEGE',
            'UNIVERSITY', 'LRN', 'GRADE', 'SECTION', 'ADDRESS', 'SIGNATURE', 'STUDENT',
            'AVENUE', 'STREET', 'LAGUNA', 'REGION', 'PROVINCE', 'MUNICIPALITY', 'BARANGAY',
            'CALABARZON',
        ];

        foreach ($blocked as $token) {
            if (str_contains($normalized, $token)) {
                return true;
            }
        }

        return (bool) preg_match('/\d{6,}/', $normalized);
    }

    private function looksLikePersonNameLine(string $normalized): bool
    {
        $tokens = preg_split('/\s+/', $normalized) ?: [];

        if (count($tokens) < 2 || count($tokens) > 7) {
            return false;
        }

        if (preg_match('/\d/', $normalized)) {
            return false;
        }

        foreach ($tokens as $token) {
            if ($this->isMiddleInitialToken($token)) {
                continue;
            }

            if (! preg_match('/^[A-Z][A-Z.\-]*$/', $token)) {
                return false;
            }
        }

        return true;
    }

    private function tokenMatches(string $left, string $right): bool
    {
        if ($left === '' || $right === '') {
            return false;
        }

        if ($left === $right) {
            return true;
        }

        similar_text($left, $right, $percent);

        return $percent >= 88.0;
    }

    private function middleNamesMatch(string $formMiddle, string $ocrMiddle, bool $strict = false): bool
    {
        if ($formMiddle === '' && $ocrMiddle === '') {
            return true;
        }

        if ($strict && $formMiddle !== '' && $ocrMiddle === '') {
            return false;
        }

        if ($formMiddle === '' || $ocrMiddle === '') {
            return ! $strict;
        }

        $formInitial = $this->middleInitial($formMiddle);
        $ocrInitial = $this->middleInitial($ocrMiddle);

        if ($formInitial !== '' && $ocrInitial !== '' && $formInitial === $ocrInitial) {
            return true;
        }

        if ($this->isMiddleInitialToken($ocrMiddle) && $formInitial === $this->normalizeMiddleInitialToken($ocrMiddle)) {
            return true;
        }

        if ($strict) {
            return false;
        }

        return $this->tokenMatches($formMiddle, $ocrMiddle);
    }

    private function ocrTextContainsFormMiddle(string $formMiddle, string $ocrText): bool
    {
        $formMiddle = $this->normalizeToken($formMiddle);

        if ($formMiddle === '') {
            return true;
        }

        if (str_contains($ocrText, $formMiddle)) {
            return true;
        }

        $initial = $this->middleInitial($formMiddle);

        if ($initial === '') {
            return false;
        }

        if ((bool) preg_match(
            '/(?:^|[\s,])'.preg_quote($initial, '/').'(?:\.(?=[\s,.]|$)|(?=[\s,.]|$))/i',
            $ocrText,
        )) {
            return true;
        }

        return (bool) preg_match(
            '/,\s*[^,]+\s+'.preg_quote($initial, '/').'\.?(?=\s|$)/i',
            $ocrText,
        );
    }

    private function isMiddleInitialToken(string $token): bool
    {
        $token = trim($token);

        if ($token === '') {
            return false;
        }

        return (bool) preg_match('/^[A-Za-z]\.?$/', $token);
    }

    private function normalizeMiddleInitialToken(string $token): string
    {
        return strtoupper(substr(trim($token), 0, 1));
    }

    private function suffixesMatch(string $formSuffix, string $ocrSuffix): bool
    {
        if ($formSuffix === '' && $ocrSuffix === '') {
            return true;
        }

        if ($formSuffix === '' || $ocrSuffix === '') {
            return true;
        }

        return $formSuffix === $ocrSuffix;
    }

    private function middleInitial(string $value): string
    {
        $value = $this->normalizeToken($value);

        if ($value === '') {
            return '';
        }

        if (strlen($value) === 1) {
            return $value;
        }

        return substr($value, 0, 1);
    }

    private function normalizeSuffix(?string $suffix): string
    {
        $suffix = $this->normalizeToken($suffix ?? '');

        if ($suffix === '' || $suffix === 'NONE') {
            return '';
        }

        return match ($suffix) {
            'JUNIOR', 'JR' => 'JR',
            'SENIOR', 'SR' => 'SR',
            default => $suffix,
        };
    }

    private function normalizeToken(string $value): string
    {
        $value = strtoupper(trim($value));
        $value = preg_replace('/[.,]/', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }
}
