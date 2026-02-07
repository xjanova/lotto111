<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Deposit Settings
    |--------------------------------------------------------------------------
    */
    'deposit' => [
        'min_amount' => (float) env('PAYMENT_MIN_DEPOSIT', 100),
        'max_amount' => (float) env('PAYMENT_MAX_DEPOSIT', 100000),
        'auto_approve' => (bool) env('PAYMENT_AUTO_APPROVE_DEPOSIT', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Withdrawal Settings
    |--------------------------------------------------------------------------
    */
    'withdrawal' => [
        'min_amount' => (float) env('PAYMENT_MIN_WITHDRAW', 300),
        'max_amount' => (float) env('PAYMENT_MAX_WITHDRAW', 50000),
        'auto_approve' => (bool) env('PAYMENT_AUTO_APPROVE_WITHDRAW', false),
        'daily_limit' => (float) env('PAYMENT_DAILY_WITHDRAW_LIMIT', 200000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Bank Accounts (Receiving)
    |--------------------------------------------------------------------------
    */
    'bank' => [
        'name' => env('BANK_NAME'),
        'account_number' => env('BANK_ACCOUNT_NUMBER'),
        'account_name' => env('BANK_ACCOUNT_NAME'),
    ],

    /*
    |--------------------------------------------------------------------------
    | PromptPay
    |--------------------------------------------------------------------------
    */
    'promptpay' => [
        'id' => env('PROMPTPAY_ID'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Banks
    |--------------------------------------------------------------------------
    */
    'banks' => [
        'KBANK' => 'ธนาคารกสิกรไทย',
        'SCB' => 'ธนาคารไทยพาณิชย์',
        'KTB' => 'ธนาคารกรุงไทย',
        'BBL' => 'ธนาคารกรุงเทพ',
        'BAY' => 'ธนาคารกรุงศรี',
        'TMB' => 'ธนาคารทหารไทยธนชาต',
        'GSB' => 'ธนาคารออมสิน',
        'BAAC' => 'ธนาคาร ธกส.',
        'CIMB' => 'ธนาคาร CIMB',
        'LHBANK' => 'ธนาคารแลนด์ แอนด์ เฮ้าส์',
        'KKP' => 'ธนาคารเกียรตินาคินภัทร',
        'ICBC' => 'ธนาคาร ICBC',
        'TISCO' => 'ธนาคารทิสโก้',
        'UOB' => 'ธนาคาร UOB',
    ],
];
