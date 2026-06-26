<?php

namespace App\Services;

use App\Models\Barangay;

class KkProfilingIdentityValidator
{
    public function __construct(
        private readonly OCRService $ocrService,
        private readonly KabataanFullNameMatcher $nameMatcher,
        private readonly PhilippineIdAddressParser $addressParser,
        private readonly BarangayAddressMatcher $barangayMatcher,
        private readonly DuplicateKabataanRegistrationService $duplicateChecker,
    ) {}

    /**
     * @param  array<string, mixed>  $registrationFields
     * @return array<string, mixed>
     */
    public function validateUploadedId(
        int $barangayId,
        array $registrationFields,
        ?string $frontImagePath,
        ?string $backImagePath,
        string $documentType = 'school_id',
        ?int $excludeRegistrationId = null,
    ): array {
        if ($frontImagePath === null || $backImagePath === null) {
            return $this->failureResult(
                errorCode: 'ocr_failed',
                message: KkProfilingValidationMessages::OCR_READ_FAILED,
            );
        }

        $frontOcr = $this->ocrService->extractText($frontImagePath);
        $backOcr = $this->ocrService->extractText($backImagePath);

        $frontText = $this->ocrText($frontOcr);
        $backText = $this->ocrText($backOcr);
        $combinedText = trim($frontText.' '.$backText);

        if ($combinedText === '') {
            return $this->failureResult(
                errorCode: 'ocr_failed',
                message: KkProfilingValidationMessages::OCR_READ_FAILED,
                ocr: [
                    'front' => $frontOcr,
                    'back' => $backOcr,
                ],
            );
        }

        $formName = $this->nameMatcher->formComponentsFromFields($registrationFields);
        $nameText = $frontText !== '' ? $frontText : $combinedText;
        $ocrName = $this->nameMatcher->parseOcrName($nameText, $formName);
        $nameMatch = $this->nameMatcher->matchesFormToOcrText($formName, $nameText, strictMiddle: true);

        if ($ocrName !== null) {
            $nameMatch = $nameMatch || $this->nameMatcher->matches($formName, $ocrName, strictMiddle: true);
        }

        if (! $nameMatch) {
            return $this->failureResult(
                errorCode: 'invalid_full_name',
                message: KkProfilingValidationMessages::INVALID_FULL_NAME,
                nameMatch: false,
                ocr: [
                    'front' => $frontOcr,
                    'back' => $backOcr,
                    'full_text' => $combinedText,
                ],
                formName: $formName,
                detectedName: $ocrName,
            );
        }

        $addressText = trim($backText !== '' ? $backText : $combinedText);
        $lines = is_array($backOcr['lines'] ?? null) ? $backOcr['lines'] : ($frontOcr['lines'] ?? []);
        $parsed = $this->addressParser->parse(is_array($lines) ? $lines : [], $addressText);

        $registrationFields['_both_sides_uploaded'] = true;
        $registrationFields['_document_type'] = $documentType;

        $barangay = Barangay::query()->find($barangayId);
        $barangayMatch = $this->barangayMatcher->matchesRegistrationAddress(
            $parsed,
            $barangay,
            $registrationFields,
            $addressText,
        );

        if (! $barangayMatch['matched']) {
            return $this->failureResult(
                errorCode: 'invalid_barangay',
                message: KkProfilingValidationMessages::INVALID_BARANGAY,
                nameMatch: true,
                barangayMatch: false,
                ocr: [
                    'front' => $frontOcr,
                    'back' => $backOcr,
                    'full_text' => $combinedText,
                ],
                formName: $formName,
                detectedName: $ocrName,
                parsedAddress: $parsed,
                matchReason: $barangayMatch['reason'],
            );
        }

        $duplicate = $this->duplicateChecker->findApprovedDuplicate(
            $barangayId,
            $registrationFields,
            $excludeRegistrationId,
        );

        if ($duplicate !== null) {
            return $this->failureResult(
                errorCode: 'duplicate',
                message: KkProfilingValidationMessages::DUPLICATE_IDENTITY,
                nameMatch: true,
                barangayMatch: true,
                duplicateDetected: true,
                ocr: [
                    'front' => $frontOcr,
                    'back' => $backOcr,
                    'full_text' => $combinedText,
                ],
                formName: $formName,
                detectedName: $ocrName,
                parsedAddress: $parsed,
                matchReason: $barangayMatch['reason'],
            );
        }

        return [
            'success' => true,
            'message' => 'School ID name and barangay match the KK Profiling form.',
            'name_match' => true,
            'barangay_match' => true,
            'duplicate_detected' => false,
            'error_code' => null,
            'document_type' => $documentType,
            'form_name' => $formName,
            'detected_name' => $ocrName,
            'address' => $parsed['address'] ?? $addressText,
            'barangay' => $parsed['barangay'] ?? null,
            'city' => $parsed['city'] ?? null,
            'province' => $parsed['province'] ?? null,
            'matched_barangay' => $barangayMatch['matched_name'],
            'registration_barangay' => $barangay?->name,
            'registration_purok_zone' => $registrationFields['purok_zone'] ?? null,
            'match_score' => $barangayMatch['score'],
            'match_reason' => $barangayMatch['reason'],
            'source' => 'id_ocr_strict',
            'ocr' => [
                'front' => $frontOcr,
                'back' => $backOcr,
                'full_text' => $combinedText,
                'average_confidence' => $backOcr['average_confidence'] ?? $frontOcr['average_confidence'] ?? null,
            ],
            'processed_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $ocr
     * @param  array<string, mixed>|null  $formName
     * @param  array<string, mixed>|null  $detectedName
     * @param  array<string, mixed>|null  $parsedAddress
     * @return array<string, mixed>
     */
    private function failureResult(
        string $errorCode,
        string $message,
        bool $nameMatch = false,
        bool $barangayMatch = false,
        bool $duplicateDetected = false,
        array $ocr = [],
        ?array $formName = null,
        ?array $detectedName = null,
        ?array $parsedAddress = null,
        ?string $matchReason = null,
    ): array {
        return [
            'success' => false,
            'message' => $message,
            'name_match' => $nameMatch,
            'barangay_match' => $barangayMatch,
            'duplicate_detected' => $duplicateDetected,
            'error_code' => $errorCode,
            'form_name' => $formName,
            'detected_name' => $detectedName,
            'parsed_address' => $parsedAddress,
            'match_reason' => $matchReason,
            'ocr' => $ocr,
            'processed_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $ocr
     */
    private function ocrText(array $ocr): string
    {
        $fullText = trim((string) ($ocr['full_text'] ?? ''));

        if ($fullText !== '') {
            return $fullText;
        }

        $lines = is_array($ocr['lines'] ?? null) ? $ocr['lines'] : [];

        return trim(implode(' ', array_map(
            fn (array $line) => trim((string) ($line['text'] ?? '')),
            $lines,
        )));
    }
}
