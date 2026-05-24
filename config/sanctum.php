<?php

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

return [
    'stateful' => explode(',', (string) env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        env('APP_URL') ? ',' . (string) parse_url((string) env('APP_URL'), PHP_URL_HOST) : '',
        env('APP_URL') ? ',' . (string) parse_url((string) env('APP_URL'), PHP_URL_HOST) . ':8000' : ''
    ))),
    'guard' => ['web'],
    'expiration' => null,
    'token_prefix' => (string) env('SANCTUM_TOKEN_PREFIX', ''),
    'middleware' => [
        'verify_csrf_token' => VerifyCsrfToken::class,
        'encrypt_cookies' => EncryptCookies::class,
    ],
];
