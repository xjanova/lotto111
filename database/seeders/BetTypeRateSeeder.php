<?php

namespace Database\Seeders;

use App\Models\BetType;
use App\Models\BetTypeRate;
use App\Models\LotteryType;
use Illuminate\Database\Seeder;

class BetTypeRateSeeder extends Seeder
{
    public function run(): void
    {
        $defaultRates = config('lottery.default_rates', [
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
        ]);

        $lotteryTypes = LotteryType::all();
        $betTypes = BetType::all()->keyBy('slug');

        foreach ($lotteryTypes as $lotteryType) {
            foreach ($defaultRates as $slug => $rate) {
                $betType = $betTypes->get($slug);
                if (! $betType) {
                    continue;
                }

                BetTypeRate::updateOrCreate(
                    [
                        'lottery_type_id' => $lotteryType->id,
                        'bet_type_id' => $betType->id,
                    ],
                    [
                        'rate' => $rate,
                        'min_amount' => 1,
                        'max_amount' => 99999,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
