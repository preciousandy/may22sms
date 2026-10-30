<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Quidax Environment Mode
    |--------------------------------------------------------------------------
    |
    | Supported: "test", "production"
    | Default is strictly "test" for safety until explicitly configured for live.
    |
    */
    'env' => env('QUIDAX_ENV', 'test'),

    /*
    |--------------------------------------------------------------------------
    | API Base URLs
    |--------------------------------------------------------------------------
    */
    'ramp_base_url' => env('QUIDAX_RAMP_BASE_URL', 'https://ramp-be.quidax.io'),
    'openapi_base_url' => env('QUIDAX_OPENAPI_BASE_URL', 'https://openapi.quidax.io/exchange-open-api/api/v1'),

    /*
    |--------------------------------------------------------------------------
    | API Credentials (Server-side Only)
    |--------------------------------------------------------------------------
    */
    'test_private_key' => env('QUIDAX_TEST_PRIVATE_KEY', 'sk_test_mock_quidax_key'),
    'live_private_key' => env('QUIDAX_LIVE_PRIVATE_KEY', null),

    'test_api_key' => env('QUIDAX_TEST_API_KEY', 'pBJTIuzX91IGEgd1FeM1BT6jH_cl5dLW'),
    'live_api_key' => env('QUIDAX_LIVE_API_KEY', env('QUIDAX_API_KEY', 'pBJTIuzX91IGEgd1FeM1BT6jH_cl5dLW')),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secrets for HMAC-SHA256 Signature Verification
    |--------------------------------------------------------------------------
    */
    'test_webhook_secret' => env('QUIDAX_TEST_WEBHOOK_SECRET', 'test_quidax_secret_key_123'),
    'live_webhook_secret' => env('QUIDAX_LIVE_WEBHOOK_SECRET', null),

    /*
    |--------------------------------------------------------------------------
    | Supported Crypto Currencies & Networks
    |--------------------------------------------------------------------------
    */
    'supported_currencies' => [
        'usdt' => [
            'name' => 'Tether USD',
            'symbol' => 'USDT',
            'decimals' => 2,
            'min_amount' => 1.0,
            'max_amount' => 50000.0,
            'networks' => ['bep20', 'trc20', 'erc20'],
            'default_network' => 'bep20',
        ],
        'usdc' => [
            'name' => 'USD Coin',
            'symbol' => 'USDC',
            'decimals' => 2,
            'min_amount' => 1.0,
            'max_amount' => 50000.0,
            'networks' => ['bep20', 'trc20', 'erc20'],
            'default_network' => 'bep20',
        ],
        'btc' => [
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'decimals' => 8,
            'min_amount' => 0.0001,
            'max_amount' => 2.0,
            'networks' => ['btc'],
            'default_network' => 'btc',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fee Settings
    |--------------------------------------------------------------------------
    */
    'platform_fee_percent' => (float) env('QUIDAX_PLATFORM_FEE_PERCENT', 0.5), // 0.5% default platform fee
    'request_timeout_seconds' => (int) env('QUIDAX_TIMEOUT', 15),
];
