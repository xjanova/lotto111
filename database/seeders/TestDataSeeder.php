<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VipLevel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // ===== ADMIN ACCOUNTS =====

        User::updateOrCreate(
            ['email' => 'superadmin@lotto.test'],
            [
                'name' => 'Super Admin',
                'phone' => '0999999999',
                'password' => Hash::make('password'),
                'role' => UserRole::SuperAdmin,
                'status' => UserStatus::Active,
                'balance' => 0,
                'vip_level' => VipLevel::Diamond,
                'xp' => 100_000,
                'referral_code' => 'SADMIN01',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@lotto.test'],
            [
                'name' => 'Admin',
                'phone' => '0888888888',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'status' => UserStatus::Active,
                'balance' => 0,
                'vip_level' => VipLevel::Gold,
                'xp' => 5_000,
                'referral_code' => 'ADMIN001',
                'email_verified_at' => now(),
            ]
        );

        // ===== AGENT ACCOUNT =====

        User::updateOrCreate(
            ['email' => 'agent@lotto.test'],
            [
                'name' => 'Agent Demo',
                'phone' => '0877777777',
                'password' => Hash::make('password'),
                'role' => UserRole::Agent,
                'status' => UserStatus::Active,
                'balance' => 50_000,
                'vip_level' => VipLevel::Platinum,
                'xp' => 20_000,
                'referral_code' => 'AGENT001',
                'email_verified_at' => now(),
            ]
        );

        // ===== MEMBER ACCOUNTS =====

        $referrer = User::updateOrCreate(
            ['email' => 'user1@lotto.test'],
            [
                'name' => 'สมชาย ทดสอบ',
                'phone' => '0811111111',
                'password' => Hash::make('password'),
                'role' => UserRole::Member,
                'status' => UserStatus::Active,
                'balance' => 5_000,
                'vip_level' => VipLevel::Silver,
                'xp' => 1_500,
                'referral_code' => 'USER0001',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'user2@lotto.test'],
            [
                'name' => 'สมหญิง ทดสอบ',
                'phone' => '0822222222',
                'password' => Hash::make('password'),
                'role' => UserRole::Member,
                'status' => UserStatus::Active,
                'balance' => 10_000,
                'vip_level' => VipLevel::Gold,
                'xp' => 8_000,
                'referral_code' => 'USER0002',
                'referred_by' => $referrer->id,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'user3@lotto.test'],
            [
                'name' => 'วิชัย เงินดี',
                'phone' => '0833333333',
                'password' => Hash::make('password'),
                'role' => UserRole::Member,
                'status' => UserStatus::Active,
                'balance' => 100_000,
                'vip_level' => VipLevel::Diamond,
                'xp' => 150_000,
                'referral_code' => 'USER0003',
                'referred_by' => $referrer->id,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'user4@lotto.test'],
            [
                'name' => 'มานี รวยมาก',
                'phone' => '0844444444',
                'password' => Hash::make('password'),
                'role' => UserRole::Member,
                'status' => UserStatus::Active,
                'balance' => 500,
                'vip_level' => VipLevel::Bronze,
                'xp' => 200,
                'referral_code' => 'USER0004',
                'email_verified_at' => now(),
            ]
        );

        // ===== SPECIAL STATUS ACCOUNTS =====

        User::updateOrCreate(
            ['email' => 'suspended@lotto.test'],
            [
                'name' => 'ยูสเซอร์ถูกระงับ',
                'phone' => '0855555555',
                'password' => Hash::make('password'),
                'role' => UserRole::Member,
                'status' => UserStatus::Suspended,
                'balance' => 2_000,
                'vip_level' => VipLevel::Silver,
                'xp' => 1_000,
                'referral_code' => 'SUSPND01',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'banned@lotto.test'],
            [
                'name' => 'ยูสเซอร์ถูกแบน',
                'phone' => '0866666666',
                'password' => Hash::make('password'),
                'role' => UserRole::Member,
                'status' => UserStatus::Banned,
                'balance' => 0,
                'vip_level' => VipLevel::Bronze,
                'xp' => 0,
                'referral_code' => 'BANNED01',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Test data seeded successfully!');
        $this->command->table(
            ['Role', 'Email', 'Password', 'Status'],
            [
                ['Super Admin', 'superadmin@lotto.test', 'password', 'Active'],
                ['Admin', 'admin@lotto.test', 'password', 'Active'],
                ['Agent', 'agent@lotto.test', 'password', 'Active'],
                ['Member', 'user1@lotto.test', 'password', 'Active'],
                ['Member', 'user2@lotto.test', 'password', 'Active'],
                ['Member (VIP Diamond)', 'user3@lotto.test', 'password', 'Active'],
                ['Member (New)', 'user4@lotto.test', 'password', 'Active'],
                ['Member', 'suspended@lotto.test', 'password', 'Suspended'],
                ['Member', 'banned@lotto.test', 'password', 'Banned'],
            ]
        );
    }
}
