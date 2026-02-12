<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General / Branding
            ['key' => 'site_name', 'value' => 'Lotto Platform', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_name_th', 'value' => 'ล็อตโต้ แพลตฟอร์ม', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_description', 'value' => 'ระบบหวยออนไลน์ครบวงจร', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_logo_url', 'value' => '', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_favicon_url', 'value' => '', 'group' => 'general', 'type' => 'string'],
            ['key' => 'primary_color', 'value' => '#3b82f6', 'group' => 'general', 'type' => 'string'],
            ['key' => 'secondary_color', 'value' => '#10b981', 'group' => 'general', 'type' => 'string'],
            ['key' => 'marquee_text', 'value' => 'ยินดีต้อนรับสู่ Lotto Platform', 'group' => 'general', 'type' => 'string'],
            ['key' => 'maintenance_mode', 'value' => '0', 'group' => 'general', 'type' => 'boolean'],
            ['key' => 'announcement', 'value' => '', 'group' => 'general', 'type' => 'text'],

            // Fees
            ['key' => 'deposit_fee_type', 'value' => 'none', 'group' => 'fees', 'type' => 'string'],
            ['key' => 'deposit_fee_fixed', 'value' => '0', 'group' => 'fees', 'type' => 'string'],
            ['key' => 'deposit_fee_percent', 'value' => '0', 'group' => 'fees', 'type' => 'string'],
            ['key' => 'deposit_fee_min', 'value' => '0', 'group' => 'fees', 'type' => 'string'],
            ['key' => 'deposit_fee_max', 'value' => '0', 'group' => 'fees', 'type' => 'string'],
            ['key' => 'withdrawal_fee_type', 'value' => 'none', 'group' => 'fees', 'type' => 'string'],
            ['key' => 'withdrawal_fee_fixed', 'value' => '0', 'group' => 'fees', 'type' => 'string'],
            ['key' => 'withdrawal_fee_percent', 'value' => '0', 'group' => 'fees', 'type' => 'string'],
            ['key' => 'withdrawal_fee_min', 'value' => '0', 'group' => 'fees', 'type' => 'string'],
            ['key' => 'withdrawal_fee_max', 'value' => '0', 'group' => 'fees', 'type' => 'string'],
            ['key' => 'free_withdrawals_per_day', 'value' => '0', 'group' => 'fees', 'type' => 'integer'],

            // Payment
            ['key' => 'min_deposit', 'value' => '100', 'group' => 'payment', 'type' => 'integer'],
            ['key' => 'max_deposit', 'value' => '100000', 'group' => 'payment', 'type' => 'integer'],
            ['key' => 'min_withdrawal', 'value' => '300', 'group' => 'payment', 'type' => 'integer'],
            ['key' => 'max_withdrawal', 'value' => '50000', 'group' => 'payment', 'type' => 'integer'],
            ['key' => 'daily_withdrawal_limit', 'value' => '200000', 'group' => 'payment', 'type' => 'integer'],
            ['key' => 'auto_approve_deposit', 'value' => '0', 'group' => 'payment', 'type' => 'boolean'],
            ['key' => 'auto_approve_max_amount', 'value' => '5000', 'group' => 'payment', 'type' => 'integer'],
            ['key' => 'auto_approve_withdrawal', 'value' => '0', 'group' => 'payment', 'type' => 'boolean'],
            ['key' => 'auto_approve_max', 'value' => '5000', 'group' => 'payment', 'type' => 'integer'],

            // Affiliate
            ['key' => 'affiliate_commission_rate', 'value' => '0.5', 'group' => 'affiliate', 'type' => 'string'],
            ['key' => 'affiliate_enabled', 'value' => '1', 'group' => 'affiliate', 'type' => 'boolean'],

            // Lottery
            ['key' => 'min_bet', 'value' => '1', 'group' => 'lottery', 'type' => 'integer'],
            ['key' => 'max_bet', 'value' => '99999', 'group' => 'lottery', 'type' => 'integer'],
            ['key' => 'max_payout_per_ticket', 'value' => '500000', 'group' => 'lottery', 'type' => 'integer'],

            // Demo Mode
            ['key' => 'demo_mode', 'value' => '0', 'group' => 'general', 'type' => 'boolean'],

            // Contact
            ['key' => 'line_id', 'value' => '', 'group' => 'contact', 'type' => 'string'],
            ['key' => 'admin_phone', 'value' => '', 'group' => 'contact', 'type' => 'string'],
            ['key' => 'footer_text', 'value' => '© 2026 Lotto Platform. All rights reserved.', 'group' => 'contact', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
