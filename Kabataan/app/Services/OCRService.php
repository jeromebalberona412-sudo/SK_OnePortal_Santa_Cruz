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
        if (! is_file($imagePath)) {
            return [
                'success' => false,
                'message' => 'Image file not found.',
            ];
        }

        $pythonResult = $this->extractWithPython($imagePath);

        if ($this->hasUsableText($pythonResult)) {
            return $pythonResult;
        }

        $windowsResult = $this->extractWithWindows($imagePath);

        if ($this->hasUsableText($windowsResult)) {
            return $windowsResult;
        }

        return $windowsResult['message'] ?? null
            ? $windowsResult
            : ($pythonResult['message'] ?? null ? $pythonResult : $windowsResult);
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
        $timeout = (int) config('ocr.timeout', 120);

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

        $payload = $this->decodeJsonOutput(trim($result->output()), trim($result->errorOutput()));

        if (! ($payload['success'] ?? false) && $this->hasUsableText($payload)) {
            $payload['success'] = true;
        }

        return $payload;
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

        try {
            $payload = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            Log::warning('Invalid OCR JSON output', ['output' => $output]);

            return [
                'success' => false,
                'message' => 'Invalid OCR response.',
            ];
        }

        if (! is_array($payload)) {
            throw new RuntimeException('OCR response must be a JSON object.');
        }

        return $payload;
    }
}
