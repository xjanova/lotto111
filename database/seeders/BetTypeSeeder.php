<?php

namespace Database\Seeders;

use App\Models\BetType;
use Illuminate\Database\Seeder;

class BetTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => '3 ตัวบน', 'slug' => 'three_top', 'digit_count' => 3, 'sort_order' => 1, 'description' => 'เลข 3 ตัวตรงกับรางวัลที่ 1'],
            ['name' => '3 ตัวโต๊ด', 'slug' => 'three_tod', 'digit_count' => 3, 'sort_order' => 2, 'description' => 'เลข 3 ตัวสลับตำแหน่ง'],
            ['name' => '3 ตัวล่าง', 'slug' => 'three_bottom', 'digit_count' => 3, 'sort_order' => 3, 'description' => 'เลข 3 ตัวล่าง'],
            ['name' => '2 ตัวบน', 'slug' => 'two_top', 'digit_count' => 2, 'sort_order' => 4, 'description' => '2 ตัวท้ายของรางวัลที่ 1'],
            ['name' => '2 ตัวล่าง', 'slug' => 'two_bottom', 'digit_count' => 2, 'sort_order' => 5, 'description' => 'เลขท้าย 2 ตัว'],
            ['name' => '2 ตัวโต๊ด', 'slug' => 'two_tod', 'digit_count' => 2, 'sort_order' => 6, 'description' => 'เลข 2 ตัวสลับตำแหน่ง'],
            ['name' => 'วิ่งบน', 'slug' => 'run_top', 'digit_count' => 1, 'sort_order' => 7, 'description' => 'เลขตัวเดียวอยู่ในรางวัลที่ 1'],
            ['name' => 'วิ่งล่าง', 'slug' => 'run_bottom', 'digit_count' => 1, 'sort_order' => 8, 'description' => 'เลขตัวเดียวอยู่ในเลขท้าย 2 ตัว'],
            ['name' => '4 ตัวบน', 'slug' => 'four_top', 'digit_count' => 4, 'sort_order' => 9, 'description' => 'เลข 4 ตัวตรง'],
            ['name' => '4 ตัวโต๊ด', 'slug' => 'four_tod', 'digit_count' => 4, 'sort_order' => 10, 'description' => 'เลข 4 ตัวสลับตำแหน่ง'],
            ['name' => '5 ตัวโต๊ด', 'slug' => 'five_tod', 'digit_count' => 5, 'sort_order' => 11, 'description' => 'เลข 5 ตัวสลับตำแหน่ง'],
        ];

        foreach ($types as $type) {
            BetType::updateOrCreate(['slug' => $type['slug']], $type + ['is_active' => true]);
        }
    }
}
