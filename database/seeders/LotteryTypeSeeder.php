<?php

namespace Database\Seeders;

use App\Models\LotteryType;
use Illuminate\Database\Seeder;

class LotteryTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // Government
            ['name' => 'หวยรัฐบาลไทย', 'slug' => 'government-thai', 'category' => 'government', 'country' => 'TH', 'sort_order' => 1],

            // Yeekee
            ['name' => 'หวยยี่กี', 'slug' => 'yeekee', 'category' => 'yeekee', 'country' => 'TH', 'sort_order' => 2],

            // Bank
            ['name' => 'หวย ธกส.', 'slug' => 'baac', 'category' => 'bank', 'country' => 'TH', 'sort_order' => 10],
            ['name' => 'หวยออมสิน', 'slug' => 'gsb', 'category' => 'bank', 'country' => 'TH', 'sort_order' => 11],

            // International
            ['name' => 'หวยลาว', 'slug' => 'laos', 'category' => 'international', 'country' => 'LA', 'sort_order' => 20],
            ['name' => 'หวยฮานอย', 'slug' => 'hanoi', 'category' => 'international', 'country' => 'VN', 'sort_order' => 21],
            ['name' => 'หวยฮานอย VIP', 'slug' => 'hanoi-vip', 'category' => 'international', 'country' => 'VN', 'sort_order' => 22],
            ['name' => 'หวยจีน', 'slug' => 'china', 'category' => 'international', 'country' => 'CN', 'sort_order' => 23],
            ['name' => 'หวยฮั่งเส็ง', 'slug' => 'hang-seng', 'category' => 'international', 'country' => 'HK', 'sort_order' => 24],
            ['name' => 'หวยนิเคอิ', 'slug' => 'nikkei', 'category' => 'international', 'country' => 'JP', 'sort_order' => 25],
            ['name' => 'หวยมาเลย์', 'slug' => 'malaysia', 'category' => 'international', 'country' => 'MY', 'sort_order' => 26],
            ['name' => 'หวยเกาหลี', 'slug' => 'korea', 'category' => 'international', 'country' => 'KR', 'sort_order' => 27],
            ['name' => 'หวยไต้หวัน', 'slug' => 'taiwan', 'category' => 'international', 'country' => 'TW', 'sort_order' => 28],
            ['name' => 'หวยสิงคโปร์', 'slug' => 'singapore', 'category' => 'international', 'country' => 'SG', 'sort_order' => 29],
            ['name' => 'หวยอังกฤษ', 'slug' => 'uk', 'category' => 'international', 'country' => 'GB', 'sort_order' => 30],
            ['name' => 'หวยเยอรมัน', 'slug' => 'germany', 'category' => 'international', 'country' => 'DE', 'sort_order' => 31],
            ['name' => 'หวยรัสเซีย', 'slug' => 'russia', 'category' => 'international', 'country' => 'RU', 'sort_order' => 32],
            ['name' => 'หวยดาวโจนส์', 'slug' => 'dowjones', 'category' => 'international', 'country' => 'US', 'sort_order' => 33],

            // Set Lottery
            ['name' => 'หวยรัฐบาล (ชุด)', 'slug' => 'government-set', 'category' => 'set', 'country' => 'TH', 'sort_order' => 50],
            ['name' => 'หวยมาเลย์ (ชุด)', 'slug' => 'malaysia-set', 'category' => 'set', 'country' => 'MY', 'sort_order' => 51],
            ['name' => 'หวยฮานอย (ชุด)', 'slug' => 'hanoi-set', 'category' => 'set', 'country' => 'VN', 'sort_order' => 52],
            ['name' => 'หวยลาวพัฒนา (ชุด)', 'slug' => 'laos-set', 'category' => 'set', 'country' => 'LA', 'sort_order' => 53],
        ];

        foreach ($types as $type) {
            LotteryType::updateOrCreate(['slug' => $type['slug']], $type + ['is_active' => true]);
        }
    }
}
