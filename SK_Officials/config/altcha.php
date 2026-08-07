<?php

return [
    'enabled' => (bool) env('ALTCHA_ENABLED', true),

    'hmac_secret' => env('ALTCHA_HMAC_SECRET'),

    'hmac_key_secret' => env('ALTCHA_HMAC_KEY_SECRET'),

    'algorithm' => env('ALTCHA_ALGORITHM', 'pbkdf2'),

    'cost' => (int) env('ALTCHA_COST', 5000),

    'expires_in' => (int) env('ALTCHA_EXPIRES_IN', 600),

    'replay_cache_store' => env('ALTCHA_CACHE_STORE'),

    'replay_cache_ttl' => (int) env('ALTCHA_REPLAY_CACHE_TTL', 900),

    'challenge_rate_limit' => (int) env('ALTCHA_CHALLENGE_RATE_LIMIT', 60),
];
