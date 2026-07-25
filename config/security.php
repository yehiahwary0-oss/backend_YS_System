<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rate Limits
    |--------------------------------------------------------------------------
    */
    'rate_limits' => [
        'public_api'     => env('RATE_LIMIT_PUBLIC_API', 120),
        'auth_attempts'  => env('RATE_LIMIT_AUTH_ATTEMPTS', 5),
        'contact'        => env('RATE_LIMIT_CONTACT', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'max_file_size' => env('MEDIA_MAX_FILE_SIZE', 10240), // KB

        // Allowed MIME types — never trust client-provided MIME
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
            'image/svg+xml',
            'application/pdf',
        ],

        // Disallowed extensions regardless of MIME
        'blocked_extensions' => [
            'php', 'php3', 'php4', 'php5', 'phtml',
            'exe', 'sh', 'bat', 'cmd', 'com',
            'js', 'ts', 'html', 'htm',
            'py', 'rb', 'pl',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    */
    'session' => [
        'admin_token_ttl_hours'        => 8,
        'admin_token_remember_days'    => 30,
    ],

];
