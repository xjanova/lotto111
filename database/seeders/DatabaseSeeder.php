<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            LotteryTypeSeeder::class,
            BetTypeSeeder::class,
            BetTypeRateSeeder::class,
            SettingsSeeder::class,
            ResultSourceSeeder::class,
        ]);
    }
}
