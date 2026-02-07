<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Checker Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the SMS Payment Checker integration
    | with the thaiprompt/smschecker-laravel package.
    |
    */

    // Maximum time difference allowed for request timestamps (in seconds)
    'timestamp_tolerance' => env('SMSCHECKER_TIMESTAMP_TOLERANCE', 300),

    // Default expiry time for unique amounts (in minutes)
    'unique_amount_expiry' => env('SMSCHECKER_AMOUNT_EXPIRY', 30),

    // Maximum number of pending unique amounts per base amount
    'max_pending_per_amount' => env('SMSCHECKER_MAX_PENDING', 99),

    // Rate limiting: max notifications per device per minute
    'rate_limit_per_minute' => env('SMSCHECKER_RATE_LIMIT', 30),

    // Nonce expiry (in hours)
    'nonce_expiry_hours' => env('SMSCHECKER_NONCE_EXPIRY', 24),

    // Auto-confirm matched payments
    'auto_confirm_matched' => env('SMSCHECKER_AUTO_CONFIRM', true),

    // Notification on match
    'notify_on_match' => env('SMSCHECKER_NOTIFY_ON_MATCH', true),

    // Log level
    'log_level' => env('SMSCHECKER_LOG_LEVEL', 'info'),

    /*
    |--------------------------------------------------------------------------
    | Receiving Bank Account
    |--------------------------------------------------------------------------
    |
    | บัญชีรับเงินที่จะแสดงให้ลูกค้าโอนเข้า
    |
    */
    'receiving_account' => [
        'bank_name' => env('DEPOSIT_BANK_NAME', 'ธนาคารกสิกรไทย'),
        'bank_code' => env('DEPOSIT_BANK_CODE', 'KBANK'),
        'account_number' => env('DEPOSIT_ACCOUNT_NUMBER', ''),
        'account_name' => env('DEPOSIT_ACCOUNT_NAME', ''),
        'promptpay_number' => env('DEPOSIT_PROMPTPAY_NUMBER', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Deposit Limits
    |--------------------------------------------------------------------------
    */
    'deposit_limits' => [
        'min_amount' => env('DEPOSIT_MIN_AMOUNT', 100),
        'max_amount' => env('DEPOSIT_MAX_AMOUNT', 50000),
        'daily_limit' => env('DEPOSIT_DAILY_LIMIT', 200000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Banks
    |--------------------------------------------------------------------------
    |
    | ธนาคารที่รองรับ (ตรงกับ smschecker Android app)
    |
    */
    'supported_banks' => [
        'KBANK' => 'ธนาคารกสิกรไทย',
        'SCB' => 'ธนาคารไทยพาณิชย์',
        'KTB' => 'ธนาคารกรุงไทย',
        'BBL' => 'ธนาคารกรุงเทพ',
        'GSB' => 'ธนาคารออมสิน',
        'BAY' => 'ธนาคารกรุงศรีอยุธยา',
        'TTB' => 'ธนาคารทหารไทยธนชาต',
        'CIMB' => 'ธนาคารซีไอเอ็มบี ไทย',
        'KKP' => 'ธนาคารเกียรตินาคินภัทร',
        'LHBANK' => 'ธนาคารแลนด์ แอนด์ เฮ้าส์',
        'TISCO' => 'ธนาคารทิสโก้',
        'UOB' => 'ธนาคารยูโอบี',
        'ICBC' => 'ธนาคารไอซีบีซี (ไทย)',
        'BAAC' => 'ธนาคารเพื่อการเกษตรฯ',
        'PROMPTPAY' => 'พร้อมเพย์',
    ],

    /*
    |--------------------------------------------------------------------------
    | WebSocket Channel
    |--------------------------------------------------------------------------
    */
    'broadcast_channel' => 'deposit-status',
];
