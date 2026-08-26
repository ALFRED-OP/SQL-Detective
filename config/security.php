<?php

return [
    'session' => [
        'lifetime' => env('SESSION_LIFETIME', 120),
        'expire_on_close' => true,
        'encrypt' => false,
        'files' => storage_path('sessions'),
        'connection' => null,
        'table' => 'sessions',
        'store' => 'file',
        'lottery' => [2, 100],
        'cookie' => env('SESSION_COOKIE', 'sqldetectivesession'),
        'path' => '/',
        'domain' => env('SESSION_DOMAIN', null),
        'secure' => env('SESSION_SECURE', true),
        'http_only' => true,
        'same_site' => 'lax',
        'partitioned' => false,
    ],

    'csrf' => [
        'enabled' => true,
        'token_name' => '_token',
        'header_name' => 'X-CSRF-TOKEN',
        'except' => [
            'api/*',
        ],
    ],

    'rate_limiting' => [
        'login' => [
            'max_attempts' => 5,
            'decay_minutes' => 15,
        ],
        'query_execution' => [
            'max_attempts' => 30,
            'decay_minutes' => 1,
        ],
        'challenge_submission' => [
            'max_attempts' => 20,
            'decay_minutes' => 1,
        ],
        'registration' => [
            'max_attempts' => 3,
            'decay_minutes' => 15,
        ],
    ],

    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => false,
        'hash_algo' => PASSWORD_DEFAULT,
        'hash_options' => [
            'cost' => 12,
        ],
    ],

    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self';",
    ],

    'query_execution' => [
        'max_query_length' => 10000,
        'max_result_rows' => 1000,
        'max_execution_time' => 5,
        'allowed_statements' => ['SELECT', 'WITH'],
        'blocked_keywords' => [
            'INSERT', 'UPDATE', 'DELETE', 'DROP', 'ALTER', 'CREATE',
            'TRUNCATE', 'GRANT', 'REVOKE', 'SET', 'LOAD DATA',
            'INTO OUTFILE', 'INTO DUMPFILE', 'CALL', 'PREPARE',
            'EXECUTE', 'DEALLOCATE', 'DESCRIBE', 'EXPLAIN',
            'SHOW', 'USE', 'COMMIT', 'ROLLBACK', 'START TRANSACTION',
            'SAVEPOINT', 'RELEASE SAVEPOINT', 'LOCK', 'UNLOCK',
            'HANDLER', 'CHECKSUM', 'CHECK', 'REPAIR', 'OPTIMIZE',
            'ANALYZE', 'FLUSH', 'RESET', 'KILL', 'PURGE',
        ],
    ],

    'encryption' => [
        'key' => env('ENCRYPTION_KEY', ''),
        'cipher' => 'AES-256-CBC',
    ],

    'audit_log' => [
        'enabled' => true,
        'retention_days' => 90,
        'log_login' => true,
        'log_logout' => true,
        'log_failed_login' => true,
        'log_password_change' => true,
        'log_query_execution' => false,
        'log_admin_actions' => true,
    ],
];