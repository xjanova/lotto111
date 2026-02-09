<?php

namespace Database\Seeders;

use App\Models\LotteryType;
use App\Models\ResultSource;
use Illuminate\Database\Seeder;

/**
 * สร้าง Result Sources เริ่มต้นสำหรับหวยทุกประเภท
 */
class ResultSourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            // === หวยรัฐบาลไทย ===
            [
                'slug' => 'government-thai',
                'provider' => 'thai_government',
                'name' => 'หวยรัฐบาลไทย (GLO API)',
                'mode' => 'auto',
                'source_url' => 'https://www.glo.or.th/api/lottery/getLatestLottery',
                'fallback_url' => 'https://lotto.api.rayriffy.com/latest',
                'scrape_config' => [
                    'method' => 'POST',
                    'content_type' => 'application/json',
                ],
                'schedule' => [
                    'draw_days' => [1, 16], // วันที่ 1 และ 16 ของเดือน
                    'draw_time' => '15:30',
                    'fetch_after_minutes' => 30, // ดึงหลังออกผล 30 นาที
                ],
                'priority' => 10,
                'timeout_seconds' => 30,
            ],

            // === หวยลาว ===
            [
                'slug' => 'laos',
                'provider' => 'lao_lottery',
                'name' => 'หวยลาว (mThai)',
                'mode' => 'auto',
                'source_url' => 'https://lotto.mthai.com/lottery/lao',
                'fallback_url' => null,
                'scrape_config' => [
                    'method' => 'GET',
                    'content_type' => 'text/html',
                ],
                'schedule' => [
                    'draw_days_of_week' => [1, 3, 5], // จันทร์ พุธ ศุกร์
                    'draw_time' => '20:30',
                    'fetch_after_minutes' => 15,
                ],
                'priority' => 8,
                'timeout_seconds' => 30,
            ],

            // === หวยฮานอย (ปกติ) ===
            [
                'slug' => 'hanoi',
                'provider' => 'hanoi_lottery',
                'name' => 'หวยฮานอย ปกติ (mThai)',
                'mode' => 'auto',
                'source_url' => 'https://lotto.mthai.com/lottery/hanoi',
                'fallback_url' => null,
                'scrape_config' => [
                    'method' => 'GET',
                    'content_type' => 'text/html',
                    'variant' => 'ปกติ',
                ],
                'schedule' => [
                    'draw_days_of_week' => [0, 1, 2, 3, 4, 5, 6], // ทุกวัน
                    'draw_time' => '18:30',
                    'fetch_after_minutes' => 15,
                ],
                'priority' => 8,
                'timeout_seconds' => 30,
            ],

            // === หวยฮานอย VIP ===
            [
                'slug' => 'hanoi-vip',
                'provider' => 'hanoi_lottery',
                'name' => 'หวยฮานอย VIP (mThai)',
                'mode' => 'auto',
                'source_url' => 'https://lotto.mthai.com/lottery/hanoi',
                'fallback_url' => null,
                'scrape_config' => [
                    'method' => 'GET',
                    'content_type' => 'text/html',
                    'variant' => 'VIP',
                ],
                'schedule' => [
                    'draw_days_of_week' => [0, 1, 2, 3, 4, 5, 6],
                    'draw_time' => '19:30',
                    'fetch_after_minutes' => 15,
                ],
                'priority' => 7,
                'timeout_seconds' => 30,
            ],

            // === หวยมาเลย์ ===
            [
                'slug' => 'malaysia',
                'provider' => 'malaysia_lottery',
                'name' => 'หวยมาเลย์ GD Lotto',
                'mode' => 'auto',
                'source_url' => 'https://4dno.org/en/',
                'fallback_url' => 'https://www.check4d.org/',
                'scrape_config' => [
                    'method' => 'GET',
                    'content_type' => 'text/html',
                    'type' => 'gd_lotto',
                ],
                'schedule' => [
                    'draw_days_of_week' => [0, 1, 2, 3, 4, 5, 6], // GD ออกทุกวัน
                    'draw_time' => '19:15', // GMT+8 = 18:15 GMT+7
                    'fetch_after_minutes' => 20,
                ],
                'priority' => 7,
                'timeout_seconds' => 30,
            ],

            // === หวยหุ้นต่างประเทศ ===
            [
                'slug' => 'nikkei',
                'provider' => 'stock_lottery',
                'name' => 'หวยนิเคอิ (lottosociety)',
                'mode' => 'auto',
                'source_url' => 'https://www.lottosociety.com/',
                'fallback_url' => null,
                'scrape_config' => ['market' => 'nikkei'],
                'schedule' => [
                    'draw_days_of_week' => [1, 2, 3, 4, 5], // จันทร์-ศุกร์
                    'draw_times' => ['09:40', '14:00'],
                    'fetch_after_minutes' => 10,
                ],
                'priority' => 5,
                'timeout_seconds' => 30,
            ],
            [
                'slug' => 'hang-seng',
                'provider' => 'stock_lottery',
                'name' => 'หวยฮั่งเส็ง (lottosociety)',
                'mode' => 'auto',
                'source_url' => 'https://www.lottosociety.com/',
                'fallback_url' => null,
                'scrape_config' => ['market' => 'hang-seng'],
                'schedule' => [
                    'draw_days_of_week' => [1, 2, 3, 4, 5],
                    'draw_times' => ['10:50', '16:30'],
                    'fetch_after_minutes' => 10,
                ],
                'priority' => 5,
                'timeout_seconds' => 30,
            ],
            [
                'slug' => 'china',
                'provider' => 'stock_lottery',
                'name' => 'หวยจีน (lottosociety)',
                'mode' => 'auto',
                'source_url' => 'https://www.lottosociety.com/',
                'fallback_url' => null,
                'scrape_config' => ['market' => 'china'],
                'schedule' => [
                    'draw_days_of_week' => [1, 2, 3, 4, 5],
                    'draw_times' => ['10:40', '15:30'],
                    'fetch_after_minutes' => 10,
                ],
                'priority' => 5,
                'timeout_seconds' => 30,
            ],
            [
                'slug' => 'dowjones',
                'provider' => 'stock_lottery',
                'name' => 'หวยดาวโจนส์ (lottosociety)',
                'mode' => 'auto',
                'source_url' => 'https://www.lottosociety.com/',
                'fallback_url' => null,
                'scrape_config' => ['market' => 'dowjones'],
                'schedule' => [
                    'draw_days_of_week' => [1, 2, 3, 4, 5],
                    'draw_time' => '04:00', // เช้า (US market close)
                    'fetch_after_minutes' => 30,
                ],
                'priority' => 5,
                'timeout_seconds' => 30,
            ],
            [
                'slug' => 'korea',
                'provider' => 'stock_lottery',
                'name' => 'หวยเกาหลี (lottosociety)',
                'mode' => 'auto',
                'source_url' => 'https://www.lottosociety.com/',
                'fallback_url' => null,
                'scrape_config' => ['market' => 'korea'],
                'schedule' => [
                    'draw_days_of_week' => [1, 2, 3, 4, 5],
                    'draw_time' => '16:00',
                    'fetch_after_minutes' => 10,
                ],
                'priority' => 5,
                'timeout_seconds' => 30,
            ],
            [
                'slug' => 'taiwan',
                'provider' => 'stock_lottery',
                'name' => 'หวยไต้หวัน (lottosociety)',
                'mode' => 'auto',
                'source_url' => 'https://www.lottosociety.com/',
                'fallback_url' => null,
                'scrape_config' => ['market' => 'taiwan'],
                'schedule' => [
                    'draw_days_of_week' => [1, 2, 3, 4, 5],
                    'draw_time' => '14:30',
                    'fetch_after_minutes' => 10,
                ],
                'priority' => 5,
                'timeout_seconds' => 30,
            ],
            [
                'slug' => 'singapore',
                'provider' => 'stock_lottery',
                'name' => 'หวยสิงคโปร์ (lottosociety)',
                'mode' => 'auto',
                'source_url' => 'https://www.lottosociety.com/',
                'fallback_url' => null,
                'scrape_config' => ['market' => 'singapore'],
                'schedule' => [
                    'draw_days_of_week' => [1, 2, 3, 4, 5],
                    'draw_time' => '17:30',
                    'fetch_after_minutes' => 10,
                ],
                'priority' => 5,
                'timeout_seconds' => 30,
            ],
            [
                'slug' => 'uk',
                'provider' => 'stock_lottery',
                'name' => 'หวยอังกฤษ (lottosociety)',
                'mode' => 'auto',
                'source_url' => 'https://www.lottosociety.com/',
                'fallback_url' => null,
                'scrape_config' => ['market' => 'uk'],
                'schedule' => [
                    'draw_days_of_week' => [1, 2, 3, 4, 5],
                    'draw_time' => '23:30',
                    'fetch_after_minutes' => 10,
                ],
                'priority' => 5,
                'timeout_seconds' => 30,
            ],
            [
                'slug' => 'germany',
                'provider' => 'stock_lottery',
                'name' => 'หวยเยอรมัน (lottosociety)',
                'mode' => 'auto',
                'source_url' => 'https://www.lottosociety.com/',
                'fallback_url' => null,
                'scrape_config' => ['market' => 'germany'],
                'schedule' => [
                    'draw_days_of_week' => [1, 2, 3, 4, 5],
                    'draw_time' => '23:30',
                    'fetch_after_minutes' => 10,
                ],
                'priority' => 5,
                'timeout_seconds' => 30,
            ],
            [
                'slug' => 'russia',
                'provider' => 'stock_lottery',
                'name' => 'หวยรัสเซีย (lottosociety)',
                'mode' => 'auto',
                'source_url' => 'https://www.lottosociety.com/',
                'fallback_url' => null,
                'scrape_config' => ['market' => 'russia'],
                'schedule' => [
                    'draw_days_of_week' => [1, 2, 3, 4, 5],
                    'draw_time' => '23:50',
                    'fetch_after_minutes' => 10,
                ],
                'priority' => 5,
                'timeout_seconds' => 30,
            ],

            // === หวยยี่กี (Internal) ===
            [
                'slug' => 'yeekee',
                'provider' => 'yeekee_internal',
                'name' => 'หวยยี่กี (Internal Engine)',
                'mode' => 'auto',
                'source_url' => null,
                'fallback_url' => null,
                'scrape_config' => [
                    'min_submissions' => 3,
                    'rounds_per_day' => 144,
                    'interval_minutes' => 10,
                ],
                'schedule' => [
                    'type' => 'interval',
                    'interval_minutes' => 10,
                ],
                'priority' => 10,
                'timeout_seconds' => 10,
            ],

            // === หวย ธกส. ===
            [
                'slug' => 'baac',
                'provider' => 'thai_government',
                'name' => 'หวย ธกส. (Manual)',
                'mode' => 'manual',
                'source_url' => null,
                'fallback_url' => null,
                'scrape_config' => [],
                'schedule' => [
                    'draw_days' => [1, 16],
                    'draw_time' => '16:30',
                ],
                'priority' => 3,
                'timeout_seconds' => 30,
            ],

            // === หวยออมสิน ===
            [
                'slug' => 'gsb',
                'provider' => 'thai_government',
                'name' => 'หวยออมสิน (Manual)',
                'mode' => 'manual',
                'source_url' => null,
                'fallback_url' => null,
                'scrape_config' => [],
                'schedule' => [
                    'draw_days' => [1, 16],
                    'draw_time' => '16:30',
                ],
                'priority' => 3,
                'timeout_seconds' => 30,
            ],
        ];

        foreach ($sources as $data) {
            $slug = $data['slug'];
            unset($data['slug']);

            $lotteryType = LotteryType::where('slug', $slug)->first();
            if (! $lotteryType) {
                continue;
            }

            ResultSource::updateOrCreate(
                ['lottery_type_id' => $lotteryType->id, 'provider' => $data['provider']],
                array_merge($data, ['lottery_type_id' => $lotteryType->id, 'is_active' => true]),
            );
        }
    }
}
