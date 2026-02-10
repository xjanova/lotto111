<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VipLevel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // ===== ADMIN ACCOUNTS =====

        User::updateOrCreate(
            ['email' => 'superadmin@lotto.test'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'phone' => '0900000001',
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
                'username' => 'admin',
                'phone' => '0900000002',
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
                'username' => 'agent01',
                'phone' => '0900000003',
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
                'username' => 'somchai',
                'phone' => '0900000011',
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
                'username' => 'somying',
                'phone' => '0900000012',
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
                'username' => 'wichai',
                'phone' => '0900000013',
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
                'username' => 'manee',
                'phone' => '0900000014',
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
                'username' => 'suspended',
                'phone' => '0900000021',
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
                'username' => 'banned',
                'phone' => '0900000022',
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
            ['Role', 'Username', 'Email', 'Password', 'Status'],
            [
                ['Super Admin', 'superadmin', 'superadmin@lotto.test', 'password', 'Active'],
                ['Admin', 'admin', 'admin@lotto.test', 'password', 'Active'],
                ['Agent', 'agent01', 'agent@lotto.test', 'password', 'Active'],
                ['Member', 'somchai', 'user1@lotto.test', 'password', 'Active'],
                ['Member', 'somying', 'user2@lotto.test', 'password', 'Active'],
                ['Member (VIP Diamond)', 'wichai', 'user3@lotto.test', 'password', 'Active'],
                ['Member (New)', 'manee', 'user4@lotto.test', 'password', 'Active'],
                ['Member', 'suspended', 'suspended@lotto.test', 'password', 'Suspended'],
                ['Member', 'banned', 'banned@lotto.test', 'password', 'Banned'],
            ]
        );
    }
}
