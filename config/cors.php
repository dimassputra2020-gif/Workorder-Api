<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'notifications/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:3001',
        'http://127.0.0.1:3000',
        // Tambahkan domain FE production kamu
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Authorization'],

    'max_age' => 0,

    'supports_credentials' => true, // ⚠️ PENTING: ubah ke true
];
