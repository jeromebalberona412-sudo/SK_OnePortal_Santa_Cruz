<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Facial verification / camera (KK Profiling registration)
    |--------------------------------------------------------------------------
    |
    | Browsers allow getUserMedia on HTTPS and on http://localhost only.
    | For LAN testing (e.g. http://192.168.x.x), use the dev HTTPS proxy:
    |   npm run serve:lan
    | then open https://YOUR_LAN_IP:8443
    |
    */

    'camera' => [
        'dev_https_port' => (int) env('KK_DEV_HTTPS_PORT', 8443),
        'dev_https_enabled' => env('KK_DEV_HTTPS_ENABLED', env('APP_ENV', 'production') === 'local'),
    ],

];
