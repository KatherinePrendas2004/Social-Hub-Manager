<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication Settings
    |--------------------------------------------------------------------------
    */

    'enabled' => env('TWO_FACTOR_ENABLED', true),

    'issuer' => env('TWO_FACTOR_ISSUER', config('app.name', 'Social Hub Manager')),

    'recovery_codes' => [
        'count' => 8,
        'length' => 8,
    ],

    'qr_code' => [
        'size' => '200x200',
        'service' => 'https://api.qrserver.com/v1/create-qr-code/',
    ],

    'rate_limiting' => [
        'max_attempts' => 5,
        'decay_minutes' => 5,
    ],
];