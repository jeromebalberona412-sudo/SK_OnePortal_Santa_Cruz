<?php

return [
    'tenant_code' => env('SK_FED_TENANT_CODE', 'santa_cruz'),
    'required_role' => env('SK_FED_REQUIRED_ROLE', 'sk_fed'),

    'trusted_device' => [
        'expiration_days' => (int) env('SK_FED_TRUSTED_DEVICE_EXPIRATION_DAYS', 30),
        'enforce_every_request' => (bool) env('SK_FED_ENFORCE_TRUSTED_DEVICE', true),
    ],

    'rate_limit' => [
        'window_minutes' => (int) env('SK_FED_LOGIN_WINDOW_MINUTES', 15),
        'max_failures_per_window' => (int) env('SK_FED_LOGIN_MAX_FAILURES', 5),
        'max_lock_minutes' => (int) env('SK_FED_LOGIN_MAX_LOCK_MINUTES', 60),
    ],

    'suspicious' => [
        'failure_threshold' => (int) env('SK_FED_SUSPICIOUS_FAILURE_THRESHOLD', 3),
        'night_start_hour' => (int) env('SK_FED_SUSPICIOUS_NIGHT_START', 0),
        'night_end_hour' => (int) env('SK_FED_SUSPICIOUS_NIGHT_END', 4),
    ],

    'retention' => [
        'attempt_days' => (int) env('SK_FED_LOGIN_ATTEMPT_RETENTION_DAYS', 90),
        'audit_days' => (int) env('SK_FED_AUDIT_RETENTION_DAYS', 180),
        'session_days' => (int) env('SK_FED_SESSION_RETENTION_DAYS', 30),
    ],

    'verification' => [
        'wait_minutes' => (int) env('SK_FED_EMAIL_VERIFICATION_WAIT_MINUTES', 15),
    ],

    'feature_flags' => [
        'features.device_verification' => (bool) env('SK_FED_FEATURE_DEVICE_VERIFICATION', true),
        'features.login_alert_notifications' => (bool) env('SK_FED_FEATURE_LOGIN_ALERTS', true),
        'features.suspicious_login_detection' => (bool) env('SK_FED_FEATURE_SUSPICIOUS_LOGIN', true),
    ],
];
