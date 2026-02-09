<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@lotto.local'],
            [
                'name' => 'Super Admin',
                'phone' => '0999999999',
                'password' => Hash::make('admin1234'),
                'role' => 'admin',
                'status' => 'active',
                'referral_code' => strtoupper(Str::random(8)),
                'email_verified_at' => now(),
            ]
        );
    }
}
