<?php

namespace App\Services;

use App\Models\Setting;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DemoModeService
{
    public const DEMO_EMAIL_DOMAIN = '@demo.lotto';

    public function isActive(): bool
    {
        return Setting::getValue('demo_mode', false);
    }

    public function activate(): array
    {
        $start = microtime(true);

        // Seed demo data
        Artisan::call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);

        // Turn on the flag
        Setting::setValue('demo_mode', '1', 'general', 'boolean');
        Cache::forget('settings:demo_mode');

        $duration = round(microtime(true) - $start, 2);
        $counts = $this->getCounts();

        return [
            'duration' => $duration,
            'counts' => $counts,
        ];
    }

    public function deactivate(): array
    {
        $start = microtime(true);

        $this->cleanupDemoData();

        Setting::setValue('demo_mode', '0', 'general', 'boolean');
        Cache::forget('settings:demo_mode');

        $duration = round(microtime(true) - $start, 2);

        return ['duration' => $duration];
    }

    public function refresh(): array
    {
        $this->cleanupDemoData();
        return $this->activate();
    }

    public function getCounts(): array
    {
        $demoUserIds = DB::table('users')
            ->where('email', 'like', '%' . self::DEMO_EMAIL_DOMAIN)
            ->pluck('id');

        return [
            'users' => $demoUserIds->count(),
            'tickets' => DB::table('tickets')->whereIn('user_id', $demoUserIds)->count(),
            'deposits' => DB::table('deposits')->whereIn('user_id', $demoUserIds)->count(),
            'withdrawals' => DB::table('withdrawals')->whereIn('user_id', $demoUserIds)->count(),
            'transactions' => DB::table('transactions')->whereIn('user_id', $demoUserIds)->count(),
        ];
    }

    private function cleanupDemoData(): void
    {
        $demoUserIds = DB::table('users')
            ->where('email', 'like', '%' . self::DEMO_EMAIL_DOMAIN)
            ->pluck('id');

        if ($demoUserIds->isEmpty()) {
            return;
        }

        // Delete in dependency order
        DB::table('ticket_items')
            ->whereIn('ticket_id', fn ($q) => $q->select('id')->from('tickets')->whereIn('user_id', $demoUserIds))
            ->delete();
        DB::table('affiliate_commissions')->whereIn('user_id', $demoUserIds)->delete();
        DB::table('affiliate_commissions')->whereIn('from_user_id', $demoUserIds)->delete();
        DB::table('tickets')->whereIn('user_id', $demoUserIds)->delete();
        DB::table('deposits')->whereIn('user_id', $demoUserIds)->delete();
        DB::table('withdrawals')->whereIn('user_id', $demoUserIds)->delete();
        DB::table('transactions')->whereIn('user_id', $demoUserIds)->delete();
        DB::table('user_bank_accounts')->whereIn('user_id', $demoUserIds)->delete();
        DB::table('user_risk_profiles')->whereIn('user_id', $demoUserIds)->delete();
        DB::table('user_gamifications')->whereIn('user_id', $demoUserIds)->delete();
        DB::table('user_badges')->whereIn('user_id', $demoUserIds)->delete();
        DB::table('user_missions')->whereIn('user_id', $demoUserIds)->delete();
        DB::table('messages')->whereIn('sender_id', $demoUserIds)->orWhereIn('receiver_id', $demoUserIds)->delete();
        DB::table('notifications')->whereIn('user_id', $demoUserIds)->delete();
        DB::table('notification_preferences')->whereIn('user_id', $demoUserIds)->delete();
        DB::table('risk_alerts')->whereIn('user_id', $demoUserIds)->delete();
        DB::table('number_sets')->whereIn('user_id', $demoUserIds)->delete();

        // Delete demo lottery rounds (code starts with DEMO-)
        $demoRoundIds = DB::table('lottery_rounds')
            ->where('round_code', 'like', 'DEMO-%')
            ->pluck('id');
        if ($demoRoundIds->isNotEmpty()) {
            DB::table('lottery_results')->whereIn('lottery_round_id', $demoRoundIds)->delete();
            DB::table('number_exposures')->whereIn('lottery_round_id', $demoRoundIds)->delete();
            DB::table('bet_limits')->whereIn('lottery_round_id', $demoRoundIds)->delete();
            DB::table('lottery_rounds')->whereIn('id', $demoRoundIds)->delete();
        }

        // Delete demo profit snapshots
        DB::table('profit_snapshots')->where('period_type', 'demo_daily')->delete();

        // Delete demo users last
        DB::table('users')->whereIn('id', $demoUserIds)->delete();
    }
}
