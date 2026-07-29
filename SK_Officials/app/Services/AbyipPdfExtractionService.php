<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;
use Throwable;

class AbyipPdfExtractionService
{
    public function __construct(private readonly Parser $parser = new Parser) {}

    /**
     * Extract plain text from a base64-encoded PDF payload.
     */
    public function extractTextFromBase64(?string $base64Pdf): string
    {
        if ($base64Pdf === null || trim($base64Pdf) === '') {
            return '';
        }

        try {
            $binary = base64_decode($base64Pdf, true);

            if ($binary === false || $binary === '') {
                return '';
            }

            $pdf = $this->parser->parseContent($binary);

            return $this->normalizeWhitespace($pdf->getText());
        } catch (Throwable $exception) {
            Log::warning('ABYIP server-side PDF text extraction failed', [
                'message' => $exception->getMessage(),
            ]);

            return '';
        }
    }

    /**
     * Merge client-side extracted text with authoritative server-side PDF text.
     * Structured tags from the client are preserved; header/budget values are corrected from server text.
     */
    public function mergeExtractedTexts(string $clientText, string $serverText): string
    {
        $clientText = trim($clientText);
        $serverText = trim($serverText);

        if ($serverText === '') {
            return $clientText;
        }

        if ($clientText === '') {
            return implode("\n", $this->appendServerHeaderTags($serverText));
        }

        $structuredLines = [];
        $plainLines = [];

        foreach (preg_split('/\R/u', $clientText) ?: [] as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            if (str_starts_with($trimmed, '@')) {
                if (! str_starts_with($trimmed, '@ABYIP_HEADER@')) {
                    $structuredLines[] = $trimmed;
                }

                continue;
            }

            $plainLines[] = $trimmed;
        }

        $merged = array_merge(
            $this->buildHeaderTagLines($serverText),
            $structuredLines,
            preg_split('/\R/u', $serverText) ?: [],
            $plainLines,
        );

        return implode("\n", array_values(array_unique($merged)));
    }

    /**
     * @return list<string>
     */
    protected function buildHeaderTagLines(string $text): array
    {
        $lines = [];
        $normalized = preg_replace('/\s+/u', ' ', $text) ?? $text;

        if (preg_match('/Barangay\s+Estimated\s+Budget\s*:?\s*₱?\s*([\d,]+\.\d{2})/iu', $normalized, $match)) {
            $lines[] = '@ABYIP_HEADER@BARANGAY_BUDGET:'.$this->normalizeAmount($match[1]);
        }

        if (preg_match('/Sangguniang\s+Kabataan\s+Fund\s*(?:(\d+(?:\.\d+)?)\s*%)?[^₱\d]*₱?\s*([\d,]+\.\d{2})/iu', $normalized, $match)) {
            $tag = '@ABYIP_HEADER@';

            if (! empty($match[1])) {
                $tag .= 'SK_FUND_PERCENT:'.$match[1];
            }

            $tag .= (! empty($match[1]) ? '|' : '').'SK_FUND_AMOUNT:'.$this->normalizeAmount($match[2]);
            $lines[] = $tag;
        }

        if (preg_match('/^TOTAL\b[^\d]*([\d,]+\.\d{2})/imu', $text, $match)) {
            $lines[] = '@ABYIP_GRAND_TOTAL@'.$this->normalizeAmount($match[1]);
        }

        return $lines;
    }

    /**
     * @return list<string>
     */
    protected function appendServerHeaderTags(string $serverText): array
    {
        $lines = preg_split('/\R/u', $serverText) ?: [];

        return array_merge($this->buildHeaderTagLines($serverText), $lines);
    }

    protected function normalizeAmount(string $value): string
    {
        return str_replace(',', '', trim($value));
    }

    protected function normalizeWhitespace(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?? $text;

        return trim($text);
    }
}
