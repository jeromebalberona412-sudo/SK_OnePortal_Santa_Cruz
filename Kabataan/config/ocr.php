<?php

return [
    'python' => env('OCR_PYTHON_PATH', PHP_OS_FAMILY === 'Windows'
        ? (is_file(base_path('python/.venv312/Scripts/python.exe'))
            ? base_path('python/.venv312/Scripts/python.exe')
            : base_path('python/.venv/Scripts/python.exe'))
        : (is_file(base_path('python/.venv312/bin/python'))
            ? base_path('python/.venv312/bin/python')
            : base_path('python/.venv/bin/python'))),

    'script' => base_path('python/ocr.py'),

    'pipeline_script' => base_path('python/validate_school_id.py'),

    'pipeline_enabled' => (bool) env('OCR_PIPELINE_ENABLED', true),

    'pipeline_timeout' => (int) env('OCR_PIPELINE_TIMEOUT', 600),

    'timeout' => (int) env('OCR_TIMEOUT', 120),

    'min_confidence' => (float) env('OCR_MIN_CONFIDENCE', 0.45),

    'min_lines' => (int) env('OCR_MIN_LINES', 2),

    'windows_script' => base_path('python/ocr_windows.ps1'),

    'trust_school_id_municipal_match' => (bool) env('OCR_TRUST_SCHOOL_ID_MUNICIPAL', false),

    'trust_complete_upload_match' => (bool) env('OCR_TRUST_COMPLETE_UPLOAD', false),
];
