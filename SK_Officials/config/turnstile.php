<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudflare Turnstile
    |--------------------------------------------------------------------------
    |
    | Configuration for the Cloudflare Turnstile human-verification widget.
    | The site key is used in the frontend widget, while the secret key is
    | used exclusively on the server for verification. Never expose the
    | secret key in frontend code or API responses.
    |
    | Set TURNSTILE_ENABLED=false in .env to bypass verification in local /
    | testing environments. It is always enforced in production.
    |
    */

    'enabled' => filter_var(env('TURNSTILE_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

    'site_key' => env('TURNSTILE_SITE_KEY', ''),

    'secret_key' => env('TURNSTILE_SECRET_KEY', ''),

    'verify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum number of seconds to wait for a response from Cloudflare's
    | verification endpoint before treating the request as failed.
    |
    */

    'timeout' => (int) env('TURNSTILE_TIMEOUT', 10),

];
