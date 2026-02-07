<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Provider
    |--------------------------------------------------------------------------
    | Supported: "thaibulksms", "thaisms", "twilio"
    */
    'provider' => env('SMS_PROVIDER', 'thaibulksms'),

    'thaibulksms' => [
        'api_key' => env('SMS_API_KEY'),
        'api_secret' => env('SMS_API_SECRET'),
        'sender' => env('SMS_SENDER_NAME', 'LottoPlatform'),
        'base_url' => 'https://bulk.thaibulksms.com/sms.php',
    ],

    'thaisms' => [
        'api_key' => env('SMS_API_KEY'),
        'api_secret' => env('SMS_API_SECRET'),
        'sender' => env('SMS_SENDER_NAME', 'LottoPlatform'),
        'base_url' => 'https://api.thaismsapi.com/v1/sms',
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Configuration
    |--------------------------------------------------------------------------
    */
    'otp' => [
        'length' => (int) env('OTP_LENGTH', 6),
        'expiry_minutes' => (int) env('OTP_EXPIRY_MINUTES', 5),
        'rate_limit' => (int) env('OTP_RATE_LIMIT', 3),
        'cooldown_seconds' => (int) env('OTP_COOLDOWN_SECONDS', 60),
        'max_attempts' => 5,
    ],
];
