<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class OCRService
{
    /**
     * @return array<string, mixed>
     */
    public function extractText(string $imagePath): array
    {
        $imagePath = $this->normalizeImagePath($imagePath);

        if (! is_file($imagePath)) {
            return [
                'success' => false,
                'message' => 'Image file not found.',
            ];
        }

        $windowsResult = PHP_OS_FAMILY === 'Windows'
            ? $this->normalizePayload($this->extractWithWindows($imagePath))
            : ['success' => false, 'lines' => [], 'full_text' => ''];

        if ($this->hasUsableText($windowsResult) && ! $this->shouldTryPythonFallback($windowsResult)) {
            return $windowsResult;
        }

        $pythonResult = $this->normalizePayload($this->extractWithPython($imagePath));

        if ($this->hasUsableText($windowsResult) && $this->hasUsableText($pythonResult)) {
            return $this->mergeOcrResults($pythonResult, $windowsResult);
        }

        if ($this->hasUsableText($pythonResult)) {
            return $pythonResult;
        }

        if ($this->hasUsableText($windowsResult)) {
            return $windowsResult;
        }

        return $this->normalizePayload(
            ($pythonResult['message'] ?? null)
                ? $pythonResult
                : (($windowsResult['message'] ?? null) ? $windowsResult : $pythonResult)
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function shouldTryPythonFallback(array $payload): bool
    {
        if (! $this->hasUsableText($payload)) {
            return true;
        }

        $text = strtolower((string) ($payload['full_text'] ?? ''));

        if ($this->textHasNameCandidate($text)) {
            return false;
        }

        return ! $this->textHasAddressCandidate($text);
    }

    private function textHasNameCandidate(string $text): bool
    {
        return (bool) preg_match(
            '/\b[A-Za-z]{2,}(?:\s+[A-Za-z]{2,}){1,4}\b/',
            $text,
        );
    }

    private function textHasAddressCandidate(string $text): bool
    {
        return (bool) preg_match(
            '/\b(sitio|purok|zone|brgy|barangay|address|sta\.?|santa\s*cruz|laguna|lag\.?)\b/i',
            $text,
        );
    }

    /**
     * @param  array<string, mixed>  $primary
     * @param  array<string, mixed>  $secondary
     * @return array<string, mixed>
     */
    private function mergeOcrResults(array $primary, array $secondary): array
    {
        $lines = [];
        $seen = [];

        foreach ([$primary, $secondary] as $payload) {
            foreach ($payload['lines'] ?? [] as $line) {
                if (! is_array($line)) {
                    continue;
                }

                $text = trim((string) ($line['text'] ?? ''));

                if ($text === '') {
                    continue;
                }

                $key = strtoupper(preg_replace('/\s+/', ' ', $text) ?? $text);

                if (isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $lines[] = [
                    'text' => $text,
                    'confidence' => (float) ($line['confidence'] ?? 0.75),
                ];
            }
        }

        $confidences = array_map(
            fn (array $line) => (float) ($line['confidence'] ?? 0.75),
            $lines,
        );

        $fullText = trim(implode(' ', array_map(
            fn (array $line) => trim((string) ($line['text'] ?? '')),
            $lines,
        )));

        return $this->normalizePayload([
            'average_confidence' => $confidences !== []
                ? round(array_sum($confidences) / count($confidences), 3)
                : 0.0,
            'lines' => $lines,
            'full_text' => $fullText,
            'engine' => trim(((string) ($primary['engine'] ?? 'python')).'+'.((string) ($secondary['engine'] ?? 'windows')), '+'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function hasUsableText(array $payload): bool
    {
        if (! empty($payload['lines']) && is_array($payload['lines'])) {
            return true;
        }

        return trim((string) ($payload['full_text'] ?? '')) !== '';
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload): array
    {
        if ($this->hasUsableText($payload)) {
            $payload['success'] = true;
        }

        return $payload;
    }

    private function normalizeImagePath(string $imagePath): string
    {
        $resolved = realpath($imagePath);

        return $resolved !== false ? $resolved : $imagePath;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractWithPython(string $imagePath): array
    {
        $python = (string) config('ocr.python');
        $script = (string) config('ocr.script');
        $timeout = (int) config('ocr.timeout', 120);

        if (! is_file($python) || ! is_file($script)) {
            return [
                'success' => false,
                'message' => 'Python OCR is not configured.',
            ];
        }

        try {
            $result = Process::timeout($timeout)
                ->run([
                    $python,
                    $script,
                    $imagePath,
                ]);
        } catch (\Throwable $exception) {
            Log::warning('Python OCR process failed', ['error' => $exception->getMessage()]);

            return [
                'success' => false,
                'message' => 'OCR processing timed out or failed.',
            ];
        }

        return $this->decodeJsonOutput(trim($result->output()), trim($result->errorOutput()));
    }

    /**
     * @return array<string, mixed>
     */
    private function extractWithWindows(string $imagePath): array
    {
        $script = (string) config('ocr.windows_script');
        $timeout = min((int) config('ocr.timeout', 120), 60);

        if (! is_file($script)) {
            return [
                'success' => false,
                'message' => 'Windows OCR script not found.',
            ];
        }

        try {
            $result = Process::timeout($timeout)
                ->run([
                    'powershell.exe',
                    '-Sta',
                    '-NoProfile',
                    '-ExecutionPolicy',
                    'Bypass',
                    '-File',
                    $script,
                    '-ImagePath',
                    $imagePath,
                ]);
        } catch (\Throwable $exception) {
            Log::warning('Windows OCR process failed', ['error' => $exception->getMessage()]);

            return [
                'success' => false,
                'message' => 'Windows OCR processing failed.',
            ];
        }

        return $this->decodeJsonOutput(trim($result->output()), trim($result->errorOutput()));
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonOutput(string $output, string $errorOutput): array
    {
        if ($output === '') {
            return [
                'success' => false,
                'message' => $errorOutput !== '' ? $errorOutput : 'OCR returned no output.',
            ];
        }

        $payload = $this->tryDecodeJson($output);

        if ($payload === null && preg_match('/\{.*\}/s', $output, $match)) {
            $payload = $this->tryDecodeJson($match[0]);
        }

        if ($payload === null) {
            Log::warning('Invalid OCR JSON output', ['output' => substr($output, 0, 500)]);

            return [
                'success' => false,
                'message' => 'Invalid OCR response.',
            ];
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function tryDecodeJson(string $output): ?array
    {
        try {
            $payload = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (! is_array($payload)) {
            throw new RuntimeException('OCR response must be a JSON object.');
        }

        return $payload;
    }
}
