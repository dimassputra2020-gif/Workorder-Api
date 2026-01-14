<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // Endpoint mana saja yang kena CORS
    'paths' => [
        'api/*',
        'notifications/*',
        'sanctum/csrf-cookie',
    ],

    // Izinkan semua method penting (atau bisa pakai ['*'])
    'allowed_methods' => [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS',
    ],

    // Sumber domain yang diizinkan (kalau masih dev / testing, ini paling longgar)
    'allowed_origins' => [
        '*',
    ],

    // Kalau mau pakai regex pattern origins (opsional)
    'allowed_origins_patterns' => [],

    // Header yang diizinkan dikirim client
    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'Accept',
        'Origin',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
    ],

    // Header yang boleh di-expose ke browser
    'exposed_headers' => [],

    // Cache preflight (OPTIONS) dalam detik, 0 = tidak dicache
    'max_age' => 0,

    // Kalau kamu pakai cookie/session cross-domain (Sanctum SPA), ini harus true,
    // TAPI catatan: kalau supports_credentials true, allowed_origins gak boleh '*'
    'supports_credentials' => false,

];
