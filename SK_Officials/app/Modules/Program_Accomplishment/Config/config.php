<?php

return [
    'statuses' => [
        'Draft',
        'Submitted',
        'Approved',
        'Rejected',
        'Published',
    ],

    'allowed_photo_mimes' => ['jpeg', 'png', 'jpg', 'gif', 'webp'],
    'allowed_document_mimes' => ['pdf', 'docx', 'doc', 'jpeg', 'png', 'jpg', 'gif', 'webp'],
    'max_photo_size' => 10240, // KB
    'max_document_size' => 20480, // KB

    'photo_path' => 'accomplishment-photos',
    'document_path' => 'accomplishment-documents',

    'pagination' => [
        'per_page' => 15,
    ],

    'public' => [
        'max_display' => 20,
    ],
];
