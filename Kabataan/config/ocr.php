<?php

$ocrRoot = env('OCR_ROOT');

if (! is_string($ocrRoot) || $ocrRoot === '' || ! is_dir($ocrRoot)) {
    $ocrRoot = dirname(base_path()).DIRECTORY_SEPARATOR.'python';
}

$defaultPython = PHP_OS_FAMILY === 'Windows'
    ? (is_file($ocrRoot.'/.venv312/Scripts/python.exe')
        ? $ocrRoot.'/.venv312/Scripts/python.exe'
        : $ocrRoot.'/.venv/Scripts/python.exe')
    : (is_file($ocrRoot.'/.venv312/bin/python')
        ? $ocrRoot.'/.venv312/bin/python'
        : $ocrRoot.'/.venv/bin/python');

return [
    'root' => $ocrRoot,

    'python' => env('OCR_PYTHON_PATH', $defaultPython),

    'script' => $ocrRoot.DIRECTORY_SEPARATOR.'ocr.py',

    'pipeline_script' => $ocrRoot.DIRECTORY_SEPARATOR.'validate_school_id.py',

    'philippine_pipeline_script' => $ocrRoot.DIRECTORY_SEPARATOR.'validate_philippine_id.py',

    'philippine_pipeline_enabled' => (bool) env('OCR_PHILIPPINE_PIPELINE_ENABLED', true),

    'pipeline_enabled' => (bool) env('OCR_PIPELINE_ENABLED', true),

    'pipeline_timeout' => (int) env('OCR_PIPELINE_TIMEOUT', 600),

    'timeout' => (int) env('OCR_TIMEOUT', 120),

    'min_confidence' => (float) env('OCR_MIN_CONFIDENCE', 0.45),

    'min_lines' => (int) env('OCR_MIN_LINES', 2),

    'windows_script' => $ocrRoot.DIRECTORY_SEPARATOR.'ocr_windows.ps1',

    'trust_school_id_municipal_match' => (bool) env('OCR_TRUST_SCHOOL_ID_MUNICIPAL', false),

    'trust_complete_upload_match' => (bool) env('OCR_TRUST_COMPLETE_UPLOAD', false),

    'api_url' => rtrim((string) env('OCR_API_URL', 'http://127.0.0.1:8001'), '/'),

    'api_enabled' => (bool) env('OCR_API_ENABLED', true),

    'api_key' => env('OCR_API_KEY'),

    'min_detect_confidence' => (float) env('OCR_MIN_DETECT_CONFIDENCE', 0.35),

    'supported_philippine_ids' => [
        'national_id',
        'philhealth_id',
        'voters_id',
    ],
];
