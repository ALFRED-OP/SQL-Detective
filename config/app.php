<?php

return [
    'name' => env('APP_NAME', 'SQL Detective'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'Asia/Kolkata',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'key' => env('APP_KEY', ''),
    'cipher' => 'AES-256-CBC',
    'maintenance_mode' => env('APP_MAINTENANCE', false),
    'maintenance_secret' => env('APP_MAINTENANCE_SECRET', ''),
    'trusted_proxies' => [],
];