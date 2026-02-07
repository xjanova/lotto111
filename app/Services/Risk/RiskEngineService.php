<?php

namespace App\Services\Risk;

use App\Enums\RiskLevel;
use App\Enums\AlertSeverity;
use App\Models\User;
use App\Models\UserRiskProfile;
use App\Models\RiskSetting;
use App\Models\RiskAlert;
use App\Models\RateAdjustmentLog;
use App\Models\NumberExposure;
use App\Models\ProfitSnapshot;
use App\Models\SystemRealtimeStat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RiskEngineService
{
    /*
    |--------------------------------------------------------------------------
    | GLOBAL WIN RATE CONTROL
    |--------------------------------------------------------------------------
    */

    /**
     * Get current system margin (กำไร % ปัจจุบันของระบบ)
     */
    public function getCurrentMargin(): array
    {
        $todayBet = $this->getStat('today_total_bet');
        $todayPayout = $this->getStat('today_total_payout');
        $grossProfit = $todayBet - $todayPayout;
        $margin = $todayBet > 0 ? ($grossProfit / $todayBet) * 100 : 0;

        return [
            'total_bet' => $todayBet,
            'total_payout' => $todayPayout,
            'gross_profit' => $grossProfit,
            'margin_percent' => round($margin, 2),
            'target_margin' => (float) $this->getRiskSetting('global_target_margin'),
            'is_on_target' => $margin >= (float) $this->getRiskSetting('global_target_margin'),
        ];
    }

    /**
     * Get real-time P&L dashboard data
     */
    public function getLiveDashboard(): array
    {
        return Cache::remember('admin:live_dashboard', 10, function () {
            $margin = $this->getCurrentMargin();

            // Monthly stats
            $monthlyStats = ProfitSnapshot::where('period_type', 'daily')
                ->where('period_start', '>=', now()->startOfMonth())
                ->selectRaw('
                    SUM(total_bet_amount) as total_bet,
                    SUM(total_payout) as total_payout,
                    SUM(gross_profit) as gross_profit,
                    SUM(total_deposit) as total_deposit,
                    SUM(total_withdraw) as total_withdraw,
                    AVG(margin_percent) as avg_margin,
                    SUM(active_users) as total_active_users,
                    SUM(new_users) as total_new_users
                ')
                ->first();

            return [
                'today' => $margin,
                'month' => [
                    'total_bet' => $monthlyStats->total_bet ?? 0,
                    'total_payout' => $monthlyStats->total_payout ?? 0,
                    'gross_profit' => $monthlyStats->gross_profit ?? 0,
                    'total_deposit' => $monthlyStats->total_deposit ?? 0,
                    'total_withdraw' => $monthlyStats->total_withdraw ?? 0,
                    'avg_margin' => round($monthlyStats->avg_margin ?? 0, 2),
                ],
                'active_users' => (int) $this->getStat('today_active_users'),
                'new_users' => (int) $this->getStat('today_new_users'),
                'open_exposure' => (float) $this->getStat('current_open_exposure'),
                'worst_case' => (float) $this->getStat('current_worst_case_payout'),
                'pending_alerts' => RiskAlert::where('status', 'new')->count(),
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | PER-USER WIN RATE CONTROL
    |--------------------------------------------------------------------------
    */

    /**
     * Get user risk profile with full stats
     */
    public function getUserRiskProfile(User $user): UserRiskProfile
    {
        return UserRiskProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['risk_level' => RiskLevel::Normal->value]
        );
    }

    /**
     * Recalculate user risk level (เรียกหลังทุกครั้งที่แทง/ถูกรางวัล)
     */
    public function recalculateUserRisk(User $user): RiskLevel
    {
        $profile = $this->getUserRiskProfile($user);

        // Calculate win rate
        $winRate = $profile->total_bet_amount > 0
            ? ($profile->total_win_amount / $profile->total_bet_amount) * 100
            : 0;

        $profile->current_win_rate = round($winRate, 2);

        // Determine risk level based on net profit for system
        $netProfit = $profile->net_profit_for_system;
        $totalBet = $profile->total_bet_amount;

        // Risk scoring
        $riskScore = 50; // baseline

        // Win rate factor
        $maxWinRate = (float) $this->getRiskSetting('global_max_win_rate');
        if ($winRate > $maxWinRate) {
            $riskScore += ($winRate - $maxWinRate) * 2;
        } elseif ($winRate < 15) {
            $riskScore -= 20;
        }

        // Net profit factor (negative = system losing money on this user)
        if ($netProfit < -50000) {
            $riskScore += 30;
        } elseif ($netProfit < -10000) {
            $riskScore += 15;
        } elseif ($netProfit > 50000) {
            $riskScore -= 15;
        }

        // Consecutive wins factor
        if ($profile->consecutive_wins >= 5) {
            $riskScore += 20;
        }

        // Bet speed factor (possible bot)
        if ($profile->bets_per_minute > 15) {
            $riskScore += 25;
        }

        // Whale detection (high volume bettor)
        $isWhale = $totalBet > 1000000;

        // Clamp score
        $riskScore = max(0, min(100, $riskScore));

        // Determine level
        $riskLevel = match (true) {
            $isWhale => RiskLevel::Whale,
            $riskScore >= 80 => RiskLevel::Danger,
            $riskScore >= 60 => RiskLevel::Watch,
            $riskScore <= 25 => RiskLevel::Fish,
            default => RiskLevel::Normal,
        };

        $profile->update([
            'risk_level' => $riskLevel->value,
            'risk_score' => $riskScore,
        ]);

        return $riskLevel;
    }

    /**
     * Set per-user win rate override (Admin action)
     */
    public function setUserWinRateOverride(User $user, ?float $winRate, int $adminId, string $reason = ''): void
    {
        $profile = $this->getUserRiskProfile($user);
        $oldValue = $profile->win_rate_override;

        $profile->update([
            'win_rate_override' => $winRate,
            'last_reviewed_by' => $adminId,
            'last_reviewed_at' => now(),
        ]);

        $this->logAdjustment('user', $user->id, 'admin', $adminId, 'win_rate_override', $oldValue, $winRate, $reason);
    }

    /**
     * Set per-user rate adjustment (Admin action)
     */
    public function setUserRateAdjustment(User $user, float $percent, int $adminId, string $reason = ''): void
    {
        $profile = $this->getUserRiskProfile($user);
        $oldValue = $profile->rate_adjustment_percent;

        $profile->update([
            'rate_adjustment_percent' => $percent,
            'last_reviewed_by' => $adminId,
            'last_reviewed_at' => now(),
        ]);

        $this->logAdjustment('user', $user->id, 'admin', $adminId, 'rate_adjustment', $oldValue, $percent, $reason);
    }

    /**
     * Block numbers for specific user
     */
    public function setUserBlockedNumbers(User $user, array $numbers, int $adminId): void
    {
        $profile = $this->getUserRiskProfile($user);
        $profile->update(['blocked_numbers' => json_encode($numbers)]);

        $this->logAdjustment('user', $user->id, 'admin', $adminId, 'blocked_numbers', null, json_encode($numbers), 'Admin blocked numbers');
    }

    /**
     * Set per-user bet limits
     */
    public function setUserBetLimits(User $user, array $limits, int $adminId): void
    {
        $profile = $this->getUserRiskProfile($user);
        $profile->update(array_filter([
            'max_bet_per_ticket' => $limits['max_bet_per_ticket'] ?? null,
            'max_bet_per_number' => $limits['max_bet_per_number'] ?? null,
            'max_payout_per_day' => $limits['max_payout_per_day'] ?? null,
            'max_payout_per_ticket' => $limits['max_payout_per_ticket'] ?? null,
            'last_reviewed_by' => $adminId,
            'last_reviewed_at' => now(),
        ]));

        $this->logAdjustment('user', $user->id, 'admin', $adminId, 'bet_limits', null, json_encode($limits), 'Admin set bet limits');
    }

    /*
    |--------------------------------------------------------------------------
    | AI AUTO-ADJUST ENGINE
    |--------------------------------------------------------------------------
    */

    /**
     * Get effective rate for a specific user + bet type
     * เรียกทุกครั้งตอนคำนวณอัตราจ่ายจริง
     */
    public function getEffectiveRate(User $user, float $baseRate): float
    {
        $profile = $this->getUserRiskProfile($user);

        if (!$this->isAutoAdjustEnabled()) {
            // Manual mode — only apply user-specific overrides
            $adjustment = $profile->rate_adjustment_percent / 100;
            return round($baseRate * (1 + $adjustment), 2);
        }

        // AI Auto-adjust mode
        $globalAdj = $this->calculateGlobalAdjustment();
        $userAdj = $this->calculateUserAdjustment($profile);

        $totalAdjustment = $globalAdj + $userAdj + ($profile->rate_adjustment_percent / 100);

        // Clamp: never adjust more than ±30%
        $totalAdjustment = max(-0.30, min(0.30, $totalAdjustment));

        $effectiveRate = $baseRate * (1 + $totalAdjustment);

        // Never go below 50% of base rate or above 120%
        $effectiveRate = max($baseRate * 0.50, min($baseRate * 1.20, $effectiveRate));

        return round($effectiveRate, 2);
    }

    /**
     * Check if user's bet is allowed (เรียกก่อนรับแทง)
     */
    public function validateBet(User $user, string $number, float $amount, int $roundId, int $betTypeId): array
    {
        $profile = $this->getUserRiskProfile($user);
        $errors = [];

        // Check user-specific blocked numbers
        $blockedNumbers = json_decode($profile->blocked_numbers ?? '[]', true);
        if (in_array($number, $blockedNumbers)) {
            $errors[] = 'เลขนี้ถูกปิดรับสำหรับบัญชีของคุณ';
        }

        // Check per-user max bet
        if ($profile->max_bet_per_ticket && $amount > $profile->max_bet_per_ticket) {
            $errors[] = "แทงได้สูงสุด {$profile->max_bet_per_ticket} บาท";
        }

        // Check daily payout cap
        if ($profile->max_payout_per_day && $profile->today_payout >= $profile->max_payout_per_day) {
            $errors[] = 'ถึงวงเงินจ่ายสูงสุดต่อวันแล้ว';
        }

        // Check global number exposure
        $exposure = NumberExposure::where('lottery_round_id', $roundId)
            ->where('bet_type_id', $betTypeId)
            ->where('number', $number)
            ->first();

        if ($exposure && $exposure->is_blocked) {
            $errors[] = 'เลขนี้ปิดรับแล้ว';
        }

        // Check bet speed (anti-bot)
        $recentBets = $profile->bets_per_minute ?? 0;
        $maxSpeed = (int) $this->getRiskSetting('anomaly_bet_speed');
        if ($recentBets > $maxSpeed) {
            $errors[] = 'กรุณารอสักครู่ก่อนแทงใหม่';
            $this->createAlert('suspected_bot', 'warning', $user->id, null,
                "Bot suspected: {$recentBets} bets/min", "User betting too fast");
        }

        return [
            'allowed' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * After bet placed — update stats & check anomalies
     */
    public function afterBetPlaced(User $user, float $amount, string $number, int $roundId, int $betTypeId): void
    {
        $profile = $this->getUserRiskProfile($user);

        // Update user stats
        $profile->increment('total_bet_amount', $amount);
        $profile->increment('total_tickets');
        $profile->increment('today_bet_amount', $amount);
        $profile->increment('today_tickets');
        $profile->update([
            'last_bet_at' => now(),
            'net_profit_for_system' => $profile->net_profit_for_system + $amount,
        ]);

        // Update system stats
        $this->incrementStat('today_total_bet', $amount);
        $this->incrementStat('today_total_tickets', 1);

        // Update number exposure
        $this->updateNumberExposure($roundId, $betTypeId, $number, $amount, $profile);

        // Check anomalies
        $this->checkBetSpeedAnomaly($user, $profile);

        // Recalculate risk
        $this->recalculateUserRisk($user);

        // Clear dashboard cache
        Cache::forget('admin:live_dashboard');
    }

    /**
     * After win — update stats & check consecutive wins
     */
    public function afterWin(User $user, float $winAmount, float $betAmount): void
    {
        $profile = $this->getUserRiskProfile($user);

        $profile->increment('total_win_amount', $winAmount);
        $profile->increment('total_wins');
        $profile->increment('today_win_amount', $winAmount);
        $profile->increment('today_payout', $winAmount);
        $profile->update([
            'consecutive_wins' => $profile->consecutive_wins + 1,
            'consecutive_losses' => 0,
            'net_profit_for_system' => $profile->net_profit_for_system - $winAmount,
        ]);

        // Update system stats
        $this->incrementStat('today_total_payout', $winAmount);
        $this->incrementStat('today_total_wins', 1);

        // Check consecutive wins
        $maxConsecutive = (int) $this->getRiskSetting('anomaly_consecutive_wins');
        if ($profile->consecutive_wins >= $maxConsecutive) {
            $this->createAlert(
                'consecutive_wins',
                'warning',
                $user->id,
                null,
                "ถูกรางวัลติดต่อกัน {$profile->consecutive_wins} ครั้ง",
                "User {$user->name} ({$user->phone}) has won {$profile->consecutive_wins} consecutive times. Total win: {$winAmount}",
                ['consecutive_wins' => $profile->consecutive_wins, 'total_win_today' => $profile->today_win_amount]
            );
        }

        // Check big win for new user
        if ($user->created_at->diffInDays(now()) < 7 && $winAmount > $profile->total_deposit * 10) {
            $this->createAlert(
                'new_user_big_win',
                'critical',
                $user->id,
                null,
                "ผู้ใช้ใหม่ถูกรางวัลเยอะ",
                "New user (registered {$user->created_at->diffInDays(now())} days ago) won {$winAmount}. Total deposit: {$profile->total_deposit}",
                ['win_amount' => $winAmount, 'total_deposit' => $profile->total_deposit]
            );
        }

        $this->recalculateUserRisk($user);
        Cache::forget('admin:live_dashboard');
    }

    /**
     * After loss — update stats
     */
    public function afterLoss(User $user): void
    {
        $profile = $this->getUserRiskProfile($user);
        $profile->update([
            'consecutive_wins' => 0,
            'consecutive_losses' => $profile->consecutive_losses + 1,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NUMBER EXPOSURE CONTROL
    |--------------------------------------------------------------------------
    */

    /**
     * Get top exposed numbers for a round (เลขที่ถ้าออกจะจ่ายหนัก)
     */
    public function getTopExposedNumbers(int $roundId, int $limit = 20): array
    {
        return NumberExposure::where('lottery_round_id', $roundId)
            ->orderByDesc('potential_payout')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Calculate worst-case payout for a round
     */
    public function getWorstCasePayout(int $roundId): float
    {
        return NumberExposure::where('lottery_round_id', $roundId)
            ->max('potential_payout') ?? 0;
    }

    /*
    |--------------------------------------------------------------------------
    | USER RANKING & ANALYTICS
    |--------------------------------------------------------------------------
    */

    /**
     * Get user profitability ranking
     */
    public function getUserProfitabilityRanking(string $sortBy = 'net_profit_for_system', string $direction = 'asc', string $period = 'all', int $limit = 50): array
    {
        $query = UserRiskProfile::with('user:id,name,phone,status')
            ->select('user_risk_profiles.*');

        if ($period === 'today') {
            $query->orderBy('today_bet_amount', 'desc');
        } else {
            $query->orderBy($sortBy, $direction);
        }

        $users = $query->limit($limit)->get();

        return [
            'users' => $users,
            'summary' => [
                'total_users' => UserRiskProfile::count(),
                'total_system_profit' => UserRiskProfile::sum('net_profit_for_system'),
                'avg_win_rate' => UserRiskProfile::avg('current_win_rate'),
                'danger_count' => UserRiskProfile::where('risk_level', 'danger')->count(),
                'watch_count' => UserRiskProfile::where('risk_level', 'watch')->count(),
                'fish_count' => UserRiskProfile::where('risk_level', 'fish')->count(),
            ],
        ];
    }

    /**
     * Get users who are winning the most (ระบบเสียมากสุด)
     */
    public function getTopWinners(string $period = 'today', int $limit = 10): array
    {
        $column = $period === 'today' ? 'today_win_amount' : 'total_win_amount';

        return UserRiskProfile::with('user:id,name,phone')
            ->orderByDesc($column)
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get users who are losing the most (ระบบได้มากสุด)
     */
    public function getTopLosers(string $period = 'today', int $limit = 10): array
    {
        return UserRiskProfile::with('user:id,name,phone')
            ->orderByDesc('net_profit_for_system')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | AI AUTO-BALANCE (เฉลี่ย Win Rate อัตโนมัติ)
    |--------------------------------------------------------------------------
    */

    /**
     * Run AI auto-balance for all users
     * เรียกทุก 5 นาที ผ่าน scheduler
     */
    public function runAutoBalance(): void
    {
        if (!$this->isAutoAdjustEnabled()) return;

        $targetWinRate = (float) $this->getRiskSetting('global_max_win_rate');
        $fishBoost = (float) $this->getRiskSetting('fish_win_rate_boost');
        $dangerCut = (float) $this->getRiskSetting('danger_win_rate_cut');

        $profiles = UserRiskProfile::where('is_auto_adjust', true)
            ->where('total_bet_amount', '>', 0)
            ->get();

        foreach ($profiles as $profile) {
            $adjustment = 0;

            switch ($profile->risk_level) {
                case 'fish':
                    // ผู้เสียมาก → เพิ่ม win rate เล็กน้อย ดึงกลับมาเล่น
                    $adjustment = $fishBoost;
                    break;

                case 'danger':
                    // ผู้ได้เยอะ → ลด win rate
                    $adjustment = -$dangerCut;
                    break;

                case 'watch':
                    // เริ่มได้เยอะ → ลดเล็กน้อย
                    $adjustment = -($dangerCut / 2);
                    break;

                default:
                    $adjustment = 0;
            }

            if ($adjustment != 0 && $adjustment != $profile->rate_adjustment_percent) {
                $oldValue = $profile->rate_adjustment_percent;
                $profile->update(['rate_adjustment_percent' => $adjustment]);

                $this->logAdjustment(
                    'user', $profile->user_id, 'ai', null,
                    'rate_adjustment', $oldValue, $adjustment,
                    "AI auto-balance: risk_level={$profile->risk_level}, win_rate={$profile->current_win_rate}%"
                );
            }
        }
    }

    /**
     * Reset daily stats (เรียกเที่ยงคืน)
     */
    public function resetDailyStats(): void
    {
        // Snapshot today's data
        $this->createDailySnapshot();

        // Reset user daily stats
        UserRiskProfile::query()->update([
            'today_bet_amount' => 0,
            'today_win_amount' => 0,
            'today_payout' => 0,
            'today_tickets' => 0,
        ]);

        // Reset system stats
        $dailyKeys = [
            'today_total_bet', 'today_total_payout', 'today_gross_profit',
            'today_margin_percent', 'today_active_users', 'today_new_users',
            'today_total_deposit', 'today_total_withdraw', 'today_total_tickets',
            'today_total_wins', 'today_avg_win_rate',
        ];

        foreach ($dailyKeys as $key) {
            SystemRealtimeStat::where('stat_key', $key)->update(['stat_value' => 0]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PRIVATE HELPERS
    |--------------------------------------------------------------------------
    */

    private function calculateGlobalAdjustment(): float
    {
        $margin = $this->getCurrentMargin();
        $target = $margin['target_margin'];
        $current = $margin['margin_percent'];

        if ($current < $target) {
            // Below target — reduce payouts
            $gap = ($target - $current) / 100;
            $sensitivity = (int) $this->getRiskSetting('adjustment_sensitivity');
            return -($gap * $sensitivity / 10);
        } elseif ($current > $target * 1.5) {
            // Way above target — slightly increase payouts to keep users playing
            return 0.01;
        }

        return 0;
    }

    private function calculateUserAdjustment(UserRiskProfile $profile): float
    {
        $maxWinRate = (float) $this->getRiskSetting('global_max_win_rate');
        $minWinRate = (float) $this->getRiskSetting('global_min_win_rate');
        $userWinRate = $profile->current_win_rate;

        if ($userWinRate > $maxWinRate) {
            return -(($userWinRate - $maxWinRate) / 100);
        } elseif ($userWinRate < $minWinRate && $profile->total_bet_amount > 10000) {
            return (($minWinRate - $userWinRate) / 200);
        }

        return 0;
    }

    private function updateNumberExposure(int $roundId, int $betTypeId, string $number, float $amount, UserRiskProfile $profile): void
    {
        $exposure = NumberExposure::firstOrCreate(
            ['lottery_round_id' => $roundId, 'bet_type_id' => $betTypeId, 'number' => $number],
            ['risk_level' => 'safe']
        );

        $exposure->increment('total_bet_amount', $amount);
        $exposure->increment('bet_count');

        // Recalculate potential payout
        $exposure->potential_payout = $exposure->total_bet_amount * $exposure->effective_rate;
        $exposure->save();

        // Auto-block check
        $this->checkAutoBlock($exposure);
    }

    private function checkAutoBlock(NumberExposure $exposure): void
    {
        $l1 = (float) $this->getRiskSetting('auto_block_threshold_l1');
        $l2 = (float) $this->getRiskSetting('auto_block_threshold_l2');
        $l3 = (float) $this->getRiskSetting('auto_block_threshold_l3');
        $l4 = (float) $this->getRiskSetting('auto_block_threshold_l4');

        $amount = $exposure->total_bet_amount;

        if ($amount >= $l4) {
            $exposure->update(['is_blocked' => true, 'risk_level' => 'critical']);
            $this->createAlert('exposure_critical', 'critical', null, $exposure->lottery_round_id,
                "เลข {$exposure->number} ถูกอั้นอัตโนมัติ (ยอด {$amount})",
                "Auto-blocked number {$exposure->number}. Total bet: {$amount}, Potential payout: {$exposure->potential_payout}");
        } elseif ($amount >= $l3) {
            $exposure->update(['rate_reduction_percent' => 50, 'risk_level' => 'critical']);
        } elseif ($amount >= $l2) {
            $exposure->update(['rate_reduction_percent' => 20, 'risk_level' => 'danger']);
        } elseif ($amount >= $l1) {
            $exposure->update(['risk_level' => 'warning']);
            $this->createAlert('high_bet_single_number', 'info', null, $exposure->lottery_round_id,
                "เลข {$exposure->number} ยอดแทงสูง ({$amount})",
                "Number {$exposure->number} high volume bet: {$amount}");
        }
    }

    private function checkBetSpeedAnomaly(User $user, UserRiskProfile $profile): void
    {
        // Count bets in last minute
        $recentCount = DB::table('tickets')
            ->where('user_id', $user->id)
            ->where('bet_at', '>=', now()->subMinute())
            ->count();

        $profile->update(['bets_per_minute' => $recentCount]);
    }

    private function createAlert(string $type, string $severity, ?int $userId, ?int $roundId, string $title, string $description, array $data = []): void
    {
        RiskAlert::create([
            'alert_type' => $type,
            'severity' => $severity,
            'user_id' => $userId,
            'lottery_round_id' => $roundId,
            'title' => $title,
            'description' => $description,
            'data' => !empty($data) ? json_encode($data) : null,
        ]);

        // Broadcast to admin dashboard
        if (in_array($severity, ['critical', 'emergency'])) {
            broadcast(new \App\Events\RiskAlertCreated($type, $severity, $title));
        }
    }

    private function logAdjustment(string $targetType, ?int $targetId, string $adjustedBy, ?int $adminId, string $field, $oldValue, $newValue, string $reason): void
    {
        RateAdjustmentLog::create([
            'target_type' => $targetType,
            'target_id' => $targetId,
            'adjusted_by' => $adjustedBy,
            'admin_id' => $adminId,
            'field_changed' => $field,
            'old_value' => is_null($oldValue) ? null : (string) $oldValue,
            'new_value' => (string) $newValue,
            'reason' => $reason,
            'context_data' => json_encode($this->getCurrentMargin()),
        ]);
    }

    private function createDailySnapshot(): void
    {
        ProfitSnapshot::create([
            'period_type' => 'daily',
            'period_start' => now()->startOfDay(),
            'period_end' => now()->endOfDay(),
            'total_bet_amount' => $this->getStat('today_total_bet'),
            'total_payout' => $this->getStat('today_total_payout'),
            'total_deposit' => $this->getStat('today_total_deposit'),
            'total_withdraw' => $this->getStat('today_total_withdraw'),
            'gross_profit' => $this->getStat('today_total_bet') - $this->getStat('today_total_payout'),
            'net_profit' => $this->getStat('today_gross_profit'),
            'margin_percent' => $this->getStat('today_margin_percent'),
            'active_users' => (int) $this->getStat('today_active_users'),
            'new_users' => (int) $this->getStat('today_new_users'),
            'total_tickets' => (int) $this->getStat('today_total_tickets'),
            'total_wins' => (int) $this->getStat('today_total_wins'),
            'avg_win_rate' => $this->getStat('today_avg_win_rate'),
        ]);
    }

    private function getRiskSetting(string $key): string
    {
        return Cache::remember("risk_setting:{$key}", 300, function () use ($key) {
            return RiskSetting::where('key', $key)->value('value') ?? '0';
        });
    }

    private function getStat(string $key): float
    {
        return (float) (SystemRealtimeStat::where('stat_key', $key)->value('stat_value') ?? 0);
    }

    private function incrementStat(string $key, float $value): void
    {
        SystemRealtimeStat::where('stat_key', $key)->increment('stat_value', $value);
    }

    private function isAutoAdjustEnabled(): bool
    {
        return $this->getRiskSetting('auto_adjust_enabled') === 'true';
    }
}
