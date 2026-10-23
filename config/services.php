<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'nowpayments' => [
        'api_key' => env('NOWPAYMENTS_API_KEY'),
        'ipn_secret' => env('NOWPAYMENTS_IPN_SECRET'),
        'api_url' => env('NOWPAYMENTS_API_URL', 'https://api.nowpayments.io/v1'),
    ],

    'paymentpoint' => [
        'api_key' => env('PAYMENTPOINT_API_KEY'),
        'api_secret' => env('PAYMENTPOINT_API_SECRET'),
        'webhook_secret' => env('PAYMENTPOINT_WEBHOOK_SECRET'),
        'api_url' => env('PAYMENTPOINT_API_URL', 'https://api.paymentpoint.example/v1'),
        'business_id' => env('PAYMENTPOINT_BUSINESS_ID'),
        'initialize_path' => env('PAYMENTPOINT_INITIALIZE_PATH', '/transactions/initialize'),
        'verify_path' => env('PAYMENTPOINT_VERIFY_PATH', ''),
        'verify_method' => env('PAYMENTPOINT_VERIFY_METHOD', 'GET'),
        'signature_header' => env('PAYMENTPOINT_SIGNATURE_HEADER', 'paymentpoint-signature'),
        'signature_algo' => env('PAYMENTPOINT_SIGNATURE_ALGO', 'sha256'),
    ],

    'strollwallet' => [
        'api_key' => env('STROLLWALLET_API_KEY'),
        'webhook_secret' => env('STROLLWALLET_WEBHOOK_SECRET'),
        'api_url' => env('STROLLWALLET_API_URL', 'https://api.strollwallet.example/v1'),
        'business_id' => env('STROLLWALLET_BUSINESS_ID'),
        'initialize_path' => env('STROLLWALLET_INITIALIZE_PATH', '/transactions/initialize'),
        'verify_path' => env('STROLLWALLET_VERIFY_PATH', ''),
        'verify_method' => env('STROLLWALLET_VERIFY_METHOD', 'GET'),
        'signature_header' => env('STROLLWALLET_SIGNATURE_HEADER', 'x-strollwallet-signature'),
        'signature_algo' => env('STROLLWALLET_SIGNATURE_ALGO', 'sha512'),
    ],

    'pocketfi' => [
        'api_key' => env('POCKETFIL_API_KEY'),
        'public_key' => env('POCKETFIL_PUBLIC_KEY'),
        'webhook_secret' => env('POCKETFIL_WEBHOOK_SECRET', env('POCKETFIL_SECRET_KEY')),
        'business_id' => env('POCKETFIL_BUSINESS_ID'),
        'api_url' => env('POCKETFIL_API_URL', 'https://api.pocketfi.ng/api/v1'),
        'auth_header_type' => env('POCKETFIL_AUTH_HEADER_TYPE', 'bearer'),
        'signature_header' => env('POCKETFIL_SIGNATURE_HEADER', 'pocketfi-signature'),
        'signature_algo' => env('POCKETFIL_SIGNATURE_ALGO', 'sha512'),
    ],

    'fx' => [
        'api_url' => env('FX_API_URL', 'https://open.er-api.com/v6/latest/USD'),
        'timeout_seconds' => (int) env('FX_TIMEOUT_SECONDS', 4),
        'cache_minutes' => (int) env('FX_CACHE_MINUTES', 30),
    ],

    'daisysms' => [
        'api_key' => env('DAISYSMS_API_KEY'),
        'base_url' => env('DAISYSMS_BASE_URL', 'https://daisysms.io/stubs/handler_api.php'),
        'proxy' => env('DAISYSMS_PROXY'),
        'flaresolverr_url' => env('DAISYSMS_FLARESOLVERR_URL'),
    ],

    'herosms' => [
        'api_key' => env('HERO_SMS_API_KEY'),
        'base_url' => env('HERO_SMS_BASE_URL', 'https://hero-sms.com/stubs/handler_api.php'),
    ],

    'fivesim' => [
        'api_key' => env('FIVESIM_API_KEY'),
    ],

    'monietude' => [
        'api_key' => env('MONIETUDE_API_KEY', 'mk_ngWDCRXg4OLw7WwT9bP4GLHqvolcH7GlW8f69Sb09cprHmeh'),
        'api_url' => env('MONIETUDE_API_URL', 'https://monietude.com/api/gateway'),
        'provider' => env('MONIETUDE_PROVIDER', 'palmpay'),
    ],

];

