<?php

return [
    /*
    | CKEditor 5 license (trial/commercial). Set CKEDITOR_LICENSE_KEY in .env.
    */
    'license_key' => env(
        'CKEDITOR_LICENSE_KEY',
        'eyJhbGciOiJFUzI1NiJ9.eyJleHAiOjE3ODA3OTAzOTksImp0aSI6IjUzYWRkNDIxLTYxYjgtNDk0NC04YWNkLWZjZmExMTA0NGRkMCIsInVzYWdlRW5kcG9pbnQiOiJodHRwczovL3Byb3h5LWV2ZW50LmNrZWRpdG9yLmNvbSIsImRpc3RyaWJ1dGlvbkNoYW5uZWwiOlsiY2xvdWQiLCJkcnVwYWwiLCJzaCJdLCJ3aGl0ZUxhYmVsIjp0cnVlLCJsaWNlbnNlVHlwZSI6InRyaWFsIiwiZmVhdHVyZXMiOlsiKiJdLCJ2YyI6ImJiNTViNzcyIn0.rTSA-0OM5NJN7Llv5B4EV5qoF7F-QyvLim_Yewougu6QF6_2hL5sSRoTj8QDophLs9VkbbSZVArUADEvoLrAxQ'
    ),

    'cloud_token_url' => env(
        'CKEDITOR_CLOUD_TOKEN_URL',
        'https://zuidoag1gct6.cke-cs.com/token/dev/f0eb94000f8469460a8b4fce05653d35f958eeb9deb42e09417a01d8a4c0?limit=10'
    ),

    'cloud_websocket_url' => env(
        'CKEDITOR_CLOUD_WEBSOCKET_URL',
        'wss://zuidoag1gct6.cke-cs.com/ws'
    ),

    'ckbox_version' => env('CKEDITOR_CKBOX_VERSION', '2.9.2'),
    'cdn_version' => env('CKEDITOR_CDN_VERSION', '47.6.1'),
];
