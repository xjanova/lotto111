<?php

return [
    /*
    |--------------------------------------------------------------------------
    | General Settings
    |--------------------------------------------------------------------------
    */
    'ticket_code_prefix' => env('LOTTERY_TICKET_PREFIX', 'TK'),
    'max_items_per_ticket' => env('LOTTERY_MAX_ITEMS_PER_TICKET', 100),
    'max_tickets_per_round' => env('LOTTERY_MAX_TICKETS_PER_ROUND', 50),

    /*
    |--------------------------------------------------------------------------
    | Yeekee Configuration
    |--------------------------------------------------------------------------
    */
    'yeekee' => [
        'rounds_per_day' => 144,
        'interval_minutes' => 10,
        'start_time' => '00:00',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Bet Limits
    |--------------------------------------------------------------------------
    */
    'default_limits' => [
        'min_bet' => 1,
        'max_bet' => 99999,
        'max_payout_per_ticket' => 500000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Payout Rates
    |--------------------------------------------------------------------------
    */
    'default_rates' => [
        'three_top' => 900,
        'three_tod' => 150,
        'three_bottom' => 450,
        'two_top' => 90,
        'two_bottom' => 90,
        'two_tod' => 13,
        'run_top' => 3.2,
        'run_bottom' => 4.2,
        'four_top' => 4000,
        'four_tod' => 25,
        'five_tod' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Government Lottery Schedule
    |--------------------------------------------------------------------------
    | วันที่ 1 และ 16 ของทุกเดือน
    */
    'government' => [
        'draw_days' => [1, 16],
        'close_before_minutes' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Result Fetch & Scraping
    |--------------------------------------------------------------------------
    */
    'auto_fetch_results' => env('LOTTERY_AUTO_FETCH', true),
    'auto_submit_scraped_results' => env('LOTTERY_AUTO_SUBMIT', true),
    'result_api_url' => env('LOTTERY_RESULT_API_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Scraper Settings
    |--------------------------------------------------------------------------
    */
    'scraper' => [
        'default_timeout' => env('SCRAPER_TIMEOUT', 30),
        'default_retries' => env('SCRAPER_RETRIES', 3),
        'default_retry_delay' => env('SCRAPER_RETRY_DELAY', 30),
        'log_retention_days' => env('SCRAPER_LOG_RETENTION', 30),
        'user_agent_rotation' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Result Sources - Default URLs
    |--------------------------------------------------------------------------
    */
    'sources' => [
        'thai_government' => [
            'primary' => env('LOTTERY_THAI_GOV_URL', 'https://www.glo.or.th/api/lottery/getLatestLottery'),
            'fallback' => env('LOTTERY_THAI_GOV_FALLBACK', 'https://lotto.api.rayriffy.com/latest'),
        ],
        'lao_lottery' => [
            'primary' => env('LOTTERY_LAO_URL', 'https://lotto.mthai.com/lottery/lao'),
        ],
        'hanoi_lottery' => [
            'primary' => env('LOTTERY_HANOI_URL', 'https://lotto.mthai.com/lottery/hanoi'),
        ],
        'malaysia_lottery' => [
            'primary' => env('LOTTERY_MALAYSIA_URL', 'https://4dno.org/en/'),
            'fallback' => env('LOTTERY_MALAYSIA_FALLBACK', 'https://www.check4d.org/'),
        ],
        'stock_lottery' => [
            'primary' => env('LOTTERY_STOCK_URL', 'https://www.lottosociety.com/'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Close Minutes Before Draw
    |--------------------------------------------------------------------------
    */
    'auto_close_minutes' => env('LOTTERY_AUTO_CLOSE_MINUTES', 5),

    /*
    |--------------------------------------------------------------------------
    | Affiliate Commission Rate (%)
    |--------------------------------------------------------------------------
    */
    'affiliate_commission_rate' => env('AFFILIATE_COMMISSION_RATE', 0.5),
];
