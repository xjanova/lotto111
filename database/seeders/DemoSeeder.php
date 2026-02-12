<?php

namespace Database\Seeders;

use App\Enums\DepositStatus;
use App\Enums\RiskLevel;
use App\Enums\RoundStatus;
use App\Enums\TicketStatus;
use App\Enums\TransactionType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VipLevel;
use App\Models\AffiliateCommission;
use App\Models\Badge;
use App\Models\BetType;
use App\Models\Deposit;
use App\Models\LotteryResult;
use App\Models\LotteryRound;
use App\Models\LotteryType;
use App\Models\Message;
use App\Models\Notification;
use App\Models\NumberExposure;
use App\Models\ProfitSnapshot;
use App\Models\RiskAlert;
use App\Models\Ticket;
use App\Models\TicketItem;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserBankAccount;
use App\Models\UserGamification;
use App\Models\UserRiskProfile;
use App\Models\Withdrawal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    private const DOMAIN = '@demo.lotto';
    private array $demoUsers = [];
    private array $demoRounds = [];
    private array $betTypes = [];
    private int $adminId;

    public function run(): void
    {
        $this->command?->info('Cleaning up old demo data...');
        app(\App\Services\DemoModeService::class)->cleanupDemoData();

        $this->command?->info('Seeding demo data...');

        // Get or create admin for references
        $admin = User::where('role', UserRole::SuperAdmin)->first()
            ?? User::where('role', UserRole::Admin)->first();
        $this->adminId = $admin?->id ?? 1;

        $this->betTypes = BetType::where('is_active', true)->get()->keyBy('slug')->toArray();

        DB::transaction(function () {
            $this->seedUsers();
            $this->seedBankAccounts();
            $this->seedRiskProfiles();
            $this->seedGamification();
            $this->seedLotteryRounds();
            $this->seedTicketsAndItems();
            $this->seedDeposits();
            $this->seedWithdrawals();
            $this->seedTransactions();
            $this->seedProfitSnapshots();
            $this->seedNumberExposures();
            $this->seedRiskAlerts();
            $this->seedAffiliateCommissions();
            $this->seedMessages();
            $this->seedNotifications();
        });

        $this->command?->info('Demo data seeded successfully!');
        $this->command?->info('  Users: ' . count($this->demoUsers));
        $this->command?->info('  Rounds: ' . count($this->demoRounds));
    }

    // ─────────────────────────────────────────
    // Users — 30 demo members
    // ─────────────────────────────────────────

    private function seedUsers(): void
    {
        $thaiNames = [
            ['สมชาย ใจดี', 'somchai_d'],
            ['สมหญิง ทองคำ', 'somying_t'],
            ['วิชัย มั่งมี', 'wichai_m'],
            ['มานี รุ่งเรือง', 'manee_r'],
            ['ประภาส สุขสม', 'prapas_s'],
            ['จันทรา พิมพ์ใจ', 'jantra_p'],
            ['สุรชัย แก้วเงิน', 'surachai_k'],
            ['นภา ศรีสุข', 'napa_si'],
            ['ธีรเดช เจริญดี', 'teeradej_j'],
            ['อรทัย ลาภมาก', 'orathai_l'],
            ['พิชิต ชนะยุทธ', 'pichit_c'],
            ['รัตนา เพชรงาม', 'rattana_p'],
            ['อนุชา ฟ้าใส', 'anucha_f'],
            ['กมลชนก ดีมาก', 'kamonchanok_d'],
            ['ณัฐพล โชคดี', 'nattapon_c'],
            ['ปาริฉัตร งามวงศ์', 'parichat_n'],
            ['ภูมิพัฒน์ ทรงพล', 'phumipat_s'],
            ['ศิริพร ราชนิยม', 'siriporn_r'],
            ['เกรียงไกร อารมณ์ดี', 'kriangkrai_a'],
            ['ขวัญจิรา สดใส', 'kwanjira_s'],
            ['วรวุฒิ มีสุข', 'worawut_m'],
            ['พรรณี ชูศรี', 'pannee_c'],
            ['ชัยวัฒน์ บุญมา', 'chaiwat_b'],
            ['ดวงใจ รักดี', 'duangjai_r'],
            ['สมศักดิ์ พลังใจ', 'somsak_p'],
            ['อารีย์ ใจงาม', 'aree_j'],
            ['ธนา ทวีทรัพย์', 'tana_t'],
            ['ยุพิน สมใจ', 'yupin_s'],
            ['พิทักษ์ ถูกรางวัล', 'pitak_t'],   // suspended
            ['วีระ แพ้บ่อย', 'weera_p'],         // banned
        ];

        $vipLevels = [VipLevel::Bronze, VipLevel::Silver, VipLevel::Gold, VipLevel::Platinum, VipLevel::Diamond];
        $balances = [500, 1200, 3500, 5000, 8000, 12000, 25000, 50000, 85000, 150000, 250000, 500000];
        $password = Hash::make('demo1234');

        foreach ($thaiNames as $i => [$name, $username]) {
            $idx = $i + 1;
            $vip = $vipLevels[min($i % 5, 4)];
            $balance = $balances[$i % count($balances)];
            $xp = $vip->minXp() + rand(0, 5000);
            $status = UserStatus::Active;
            if ($i === 28) $status = UserStatus::Suspended;
            if ($i === 29) $status = UserStatus::Banned;

            $user = User::updateOrCreate(
                ['email' => "demo_{$idx}" . self::DOMAIN],
                [
                    'name' => $name,
                    'username' => "demo_{$username}",
                    'phone' => '08' . str_pad((string) (10000000 + $idx), 8, '0', STR_PAD_LEFT),
                    'password' => $password,
                    'role' => UserRole::Member,
                    'status' => $status,
                    'balance' => $balance,
                    'vip_level' => $vip,
                    'xp' => $xp,
                    'referral_code' => 'DEMO' . str_pad((string) $idx, 4, '0', STR_PAD_LEFT),
                    'referred_by' => ($i > 0 && $i <= 10) ? ($this->demoUsers[0]['id'] ?? null) : null,
                    'email_verified_at' => now(),
                    'last_login_at' => now()->subHours(rand(1, 168)),
                    'last_login_ip' => '192.168.1.' . rand(10, 250),
                ]
            );

            $this->demoUsers[] = $user->toArray();
        }
    }

    // ─────────────────────────────────────────
    // Bank Accounts
    // ─────────────────────────────────────────

    private function seedBankAccounts(): void
    {
        $banks = [
            ['kbank', 'ธนาคารกสิกรไทย'],
            ['scb', 'ธนาคารไทยพาณิชย์'],
            ['bbl', 'ธนาคารกรุงเทพ'],
            ['ktb', 'ธนาคารกรุงไทย'],
            ['bay', 'ธนาคารกรุงศรีอยุธยา'],
            ['ttb', 'ธนาคารทหารไทยธนชาต'],
        ];

        foreach ($this->demoUsers as $i => $user) {
            if ($user['status'] === UserStatus::Banned->value) continue;

            $bank = $banks[$i % count($banks)];
            UserBankAccount::updateOrCreate(
                ['user_id' => $user['id'], 'bank_code' => $bank[0]],
                [
                    'bank_name' => $bank[1],
                    'account_number' => str_pad((string) rand(1000000000, 9999999999), 10, '0'),
                    'account_name' => $user['name'],
                    'is_primary' => true,
                ]
            );
        }
    }

    // ─────────────────────────────────────────
    // Risk Profiles
    // ─────────────────────────────────────────

    private function seedRiskProfiles(): void
    {
        $riskLevels = [RiskLevel::Fish, RiskLevel::Normal, RiskLevel::Watch, RiskLevel::Danger, RiskLevel::Whale];

        foreach ($this->demoUsers as $i => $user) {
            $rl = $riskLevels[$i % count($riskLevels)];
            $totalBet = rand(5000, 500000);
            $totalWin = (int) ($totalBet * (rand(60, 130) / 100));

            UserRiskProfile::updateOrCreate(
                ['user_id' => $user['id']],
                [
                    'risk_level' => $rl,
                    'risk_score' => rand(0, 100),
                    'current_win_rate' => rand(20, 80) / 100,
                    'is_auto_adjust' => true,
                    'total_bet_amount' => $totalBet,
                    'total_win_amount' => $totalWin,
                    'total_deposit' => rand(10000, 300000),
                    'total_tickets' => rand(20, 500),
                    'total_wins' => rand(5, 100),
                    'consecutive_wins' => rand(0, 5),
                    'consecutive_losses' => rand(0, 10),
                    'today_bet_amount' => rand(0, 10000),
                    'today_win_amount' => rand(0, 5000),
                    'today_tickets' => rand(0, 20),
                    'net_profit_for_system' => $totalBet - $totalWin,
                    'last_bet_at' => now()->subMinutes(rand(5, 1440)),
                ]
            );
        }
    }

    // ─────────────────────────────────────────
    // Gamification
    // ─────────────────────────────────────────

    private function seedGamification(): void
    {
        foreach ($this->demoUsers as $i => $user) {
            UserGamification::updateOrCreate(
                ['user_id' => $user['id']],
                [
                    'xp' => $user['xp'] ?? rand(100, 50000),
                    'login_streak' => rand(1, 30),
                    'longest_streak' => rand(10, 60),
                    'last_daily_claim' => now()->subDays(rand(0, 2)),
                    'spin_count' => rand(0, 10),
                ]
            );

            // Assign some badges
            $badges = Badge::inRandomOrder()->limit(rand(1, 3))->pluck('id');
            foreach ($badges as $badgeId) {
                DB::table('user_badges')->insertOrIgnore([
                    'user_id' => $user['id'],
                    'badge_id' => $badgeId,
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    // ─────────────────────────────────────────
    // Lottery Rounds — 14 days back + today + future
    // ─────────────────────────────────────────

    private function seedLotteryRounds(): void
    {
        $lotteryTypes = LotteryType::where('is_active', true)->get();
        if ($lotteryTypes->isEmpty()) return;

        // Pick top 6 diverse types for demo rounds
        $selectedTypes = $lotteryTypes->groupBy(fn ($lt) => $lt->category->value ?? $lt->category)
            ->flatMap(fn ($group) => $group->take(2))
            ->take(8);

        $roundNum = 1;

        foreach ($selectedTypes as $lt) {
            // Past rounds (resulted) — one per day for last 7 days
            for ($day = 7; $day >= 1; $day--) {
                $date = now()->subDays($day);
                $round = LotteryRound::create([
                    'lottery_type_id' => $lt->id,
                    'round_code' => 'DEMO-' . $lt->slug . '-' . $date->format('Ymd'),
                    'round_number' => $roundNum++,
                    'status' => RoundStatus::Resulted,
                    'open_at' => $date->copy()->setTime(8, 0),
                    'close_at' => $date->copy()->setTime(15, 0),
                    'result_at' => $date->copy()->setTime(15, 30),
                ]);

                // Create results
                $this->createDemoResult($round);
                $this->demoRounds[] = $round->toArray();
            }

            // Today: 1 open round
            $round = LotteryRound::create([
                'lottery_type_id' => $lt->id,
                'round_code' => 'DEMO-' . $lt->slug . '-' . now()->format('Ymd') . '-OPEN',
                'round_number' => $roundNum++,
                'status' => RoundStatus::Open,
                'open_at' => now()->setTime(8, 0),
                'close_at' => now()->setTime(22, 0),
                'result_at' => null,
            ]);
            $this->demoRounds[] = $round->toArray();

            // Tomorrow: 1 upcoming
            $tomorrow = now()->addDay();
            $round = LotteryRound::create([
                'lottery_type_id' => $lt->id,
                'round_code' => 'DEMO-' . $lt->slug . '-' . $tomorrow->format('Ymd'),
                'round_number' => $roundNum++,
                'status' => RoundStatus::Upcoming,
                'open_at' => $tomorrow->copy()->setTime(8, 0),
                'close_at' => $tomorrow->copy()->setTime(15, 0),
                'result_at' => null,
            ]);
            $this->demoRounds[] = $round->toArray();
        }
    }

    private function createDemoResult(LotteryRound $round): void
    {
        // Generate realistic result values
        $three = str_pad((string) rand(0, 999), 3, '0', STR_PAD_LEFT);
        $two = substr($three, -2);

        LotteryResult::create([
            'lottery_round_id' => $round->id,
            'result_type' => 'three_digit_top',
            'result_value' => $three,
        ]);
        LotteryResult::create([
            'lottery_round_id' => $round->id,
            'result_type' => 'two_digit_top',
            'result_value' => $two,
        ]);
        LotteryResult::create([
            'lottery_round_id' => $round->id,
            'result_type' => 'two_digit_bottom',
            'result_value' => str_pad((string) rand(0, 99), 2, '0', STR_PAD_LEFT),
        ]);
    }

    // ─────────────────────────────────────────
    // Tickets & Items
    // ─────────────────────────────────────────

    private function seedTicketsAndItems(): void
    {
        $resultedRounds = collect($this->demoRounds)->where('status', RoundStatus::Resulted->value);
        $openRounds = collect($this->demoRounds)->where('status', RoundStatus::Open->value);
        $activeUsers = collect($this->demoUsers)->where('status', UserStatus::Active->value);

        if ($activeUsers->isEmpty() || empty($this->betTypes)) return;

        $betTypeList = array_values($this->betTypes);
        $ticketNum = 1;

        // Tickets for resulted rounds
        foreach ($resultedRounds as $round) {
            $numTickets = rand(3, 8);
            for ($t = 0; $t < $numTickets; $t++) {
                $user = $activeUsers->random();
                $bt = $betTypeList[array_rand($betTypeList)];
                $digits = $bt['digit_count'] ?? 2;
                $maxNum = (int) str_repeat('9', $digits);
                $number = str_pad((string) rand(0, $maxNum), $digits, '0', STR_PAD_LEFT);
                $amount = [10, 20, 50, 100, 200, 500, 1000][array_rand([10, 20, 50, 100, 200, 500, 1000])];
                $isWon = rand(1, 100) <= 15; // 15% win rate
                $rate = $this->getRateForBetType($bt['slug']);
                $winAmount = $isWon ? $amount * $rate : 0;

                $status = $isWon ? TicketStatus::Won : TicketStatus::Lost;
                $roundDate = Carbon::parse($round['close_at']);

                $ticket = Ticket::create([
                    'user_id' => $user['id'],
                    'lottery_round_id' => $round['id'],
                    'bet_type_id' => $bt['id'],
                    'ticket_code' => 'DEMO-T' . str_pad((string) $ticketNum++, 6, '0', STR_PAD_LEFT),
                    'number' => $number,
                    'amount' => $amount,
                    'rate' => $rate,
                    'total_amount' => $amount,
                    'total_win' => $winAmount,
                    'win_amount' => $winAmount,
                    'status' => $status,
                    'bet_at' => $roundDate->copy()->subHours(rand(1, 6)),
                    'result_at' => $roundDate,
                    'created_at' => $roundDate->copy()->subHours(rand(1, 6)),
                    'updated_at' => $roundDate,
                ]);

                TicketItem::create([
                    'ticket_id' => $ticket->id,
                    'bet_type_id' => $bt['id'],
                    'number' => $number,
                    'amount' => $amount,
                    'rate' => $rate,
                    'win_amount' => $winAmount,
                    'is_won' => $isWon,
                ]);
            }
        }

        // Pending tickets for open rounds
        foreach ($openRounds as $round) {
            $numTickets = rand(5, 15);
            for ($t = 0; $t < $numTickets; $t++) {
                $user = $activeUsers->random();
                $bt = $betTypeList[array_rand($betTypeList)];
                $digits = $bt['digit_count'] ?? 2;
                $maxNum = (int) str_repeat('9', $digits);
                $number = str_pad((string) rand(0, $maxNum), $digits, '0', STR_PAD_LEFT);
                $amount = [10, 20, 50, 100, 200, 500][array_rand([10, 20, 50, 100, 200, 500])];
                $rate = $this->getRateForBetType($bt['slug']);

                $ticket = Ticket::create([
                    'user_id' => $user['id'],
                    'lottery_round_id' => $round['id'],
                    'bet_type_id' => $bt['id'],
                    'ticket_code' => 'DEMO-T' . str_pad((string) $ticketNum++, 6, '0', STR_PAD_LEFT),
                    'number' => $number,
                    'amount' => $amount,
                    'rate' => $rate,
                    'total_amount' => $amount,
                    'total_win' => 0,
                    'win_amount' => 0,
                    'status' => TicketStatus::Pending,
                    'bet_at' => now()->subMinutes(rand(5, 300)),
                    'result_at' => null,
                    'created_at' => now()->subMinutes(rand(5, 300)),
                    'updated_at' => now(),
                ]);

                TicketItem::create([
                    'ticket_id' => $ticket->id,
                    'bet_type_id' => $bt['id'],
                    'number' => $number,
                    'amount' => $amount,
                    'rate' => $rate,
                    'win_amount' => 0,
                    'is_won' => false,
                ]);
            }
        }
    }

    private function getRateForBetType(string $slug): float
    {
        return match ($slug) {
            '3top' => 900,
            '3tod' => 150,
            '3bottom' => 450,
            '2top' => 90,
            '2bottom' => 90,
            '2tod' => 13,
            'run_top' => 3.2,
            'run_bottom' => 4.2,
            '4top' => 4000,
            '4tod' => 25,
            '5tod' => 15,
            default => 90,
        };
    }

    // ─────────────────────────────────────────
    // Deposits
    // ─────────────────────────────────────────

    private function seedDeposits(): void
    {
        $activeUsers = collect($this->demoUsers)->where('status', UserStatus::Active->value);
        if ($activeUsers->isEmpty()) return;

        $amounts = [100, 200, 300, 500, 1000, 2000, 3000, 5000, 10000, 20000, 50000];
        $statuses = array_merge(
            array_fill(0, 70, 'credited'),
            array_fill(0, 12, 'pending'),
            array_fill(0, 10, 'approved'),
            array_fill(0, 8, 'rejected'),
        );

        for ($i = 0; $i < 100; $i++) {
            $user = $activeUsers->random();
            $amount = $amounts[array_rand($amounts)];
            $status = $statuses[array_rand($statuses)];
            $daysAgo = rand(0, 13);
            $date = now()->subDays($daysAgo)->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            Deposit::create([
                'user_id' => $user['id'],
                'amount' => $amount,
                'unique_amount' => $amount + (rand(1, 99) / 100),
                'method' => 'sms_auto',
                'status' => $status,
                'matched_at' => in_array($status, ['credited', 'approved']) ? $date->copy()->addMinutes(rand(1, 30)) : null,
                'credited_at' => $status === 'credited' ? $date->copy()->addMinutes(rand(2, 35)) : null,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }

    // ─────────────────────────────────────────
    // Withdrawals
    // ─────────────────────────────────────────

    private function seedWithdrawals(): void
    {
        $activeUsers = collect($this->demoUsers)->where('status', UserStatus::Active->value);
        if ($activeUsers->isEmpty()) return;

        $amounts = [300, 500, 1000, 2000, 3000, 5000, 10000, 20000, 30000];
        $statuses = array_merge(
            array_fill(0, 25, 'completed'),
            array_fill(0, 10, 'pending'),
            array_fill(0, 8, 'approved'),
            array_fill(0, 7, 'rejected'),
        );

        for ($i = 0; $i < 50; $i++) {
            $user = $activeUsers->random();
            $amount = $amounts[array_rand($amounts)];
            $status = $statuses[array_rand($statuses)];
            $daysAgo = rand(0, 13);
            $date = now()->subDays($daysAgo)->subHours(rand(0, 23));

            $bankAccount = UserBankAccount::where('user_id', $user['id'])->first();

            Withdrawal::create([
                'user_id' => $user['id'],
                'bank_account_id' => $bankAccount?->id,
                'amount' => $amount,
                'status' => $status,
                'approved_by' => in_array($status, ['approved', 'completed']) ? $this->adminId : null,
                'approved_at' => in_array($status, ['approved', 'completed']) ? $date->copy()->addMinutes(rand(5, 60)) : null,
                'completed_at' => $status === 'completed' ? $date->copy()->addMinutes(rand(10, 120)) : null,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }

    // ─────────────────────────────────────────
    // Transactions — financial trail
    // ─────────────────────────────────────────

    private function seedTransactions(): void
    {
        $activeUsers = collect($this->demoUsers)->where('status', UserStatus::Active->value);
        if ($activeUsers->isEmpty()) return;

        foreach ($activeUsers as $user) {
            $balance = 0.0;
            $numTx = rand(10, 40);

            for ($t = 0; $t < $numTx; $t++) {
                $daysAgo = rand(0, 13);
                $date = now()->subDays($daysAgo)->subHours(rand(0, 23))->subMinutes(rand(0, 59));
                $types = [TransactionType::Deposit, TransactionType::Bet, TransactionType::Bet, TransactionType::Win, TransactionType::Withdraw];
                $type = $types[array_rand($types)];

                $amount = match ($type) {
                    TransactionType::Deposit => rand(1, 50) * 100,
                    TransactionType::Withdraw => -rand(3, 100) * 100,
                    TransactionType::Bet => -rand(1, 50) * 10,
                    TransactionType::Win => rand(1, 200) * 10,
                    default => rand(1, 100) * 10,
                };

                $balanceBefore = max(0, $balance);
                $balance = max(0, $balance + $amount);

                $desc = match ($type) {
                    TransactionType::Deposit => 'ฝากเงิน ฿' . number_format(abs($amount)),
                    TransactionType::Withdraw => 'ถอนเงิน ฿' . number_format(abs($amount)),
                    TransactionType::Bet => 'แทงหวย',
                    TransactionType::Win => 'ถูกรางวัล',
                    default => 'ปรับยอด',
                };

                Transaction::create([
                    'user_id' => $user['id'],
                    'type' => $type,
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balance,
                    'description' => $desc,
                    'created_at' => $date,
                ]);
            }
        }
    }

    // ─────────────────────────────────────────
    // Profit Snapshots — 14 days
    // ─────────────────────────────────────────

    private function seedProfitSnapshots(): void
    {
        for ($day = 13; $day >= 0; $day--) {
            $date = now()->subDays($day)->startOfDay();
            $totalBet = rand(50000, 500000);
            $totalPayout = (int) ($totalBet * (rand(70, 95) / 100));
            $totalDeposit = rand(80000, 600000);
            $totalWithdraw = rand(30000, 300000);

            ProfitSnapshot::create([
                'period_type' => 'demo_daily',
                'period_start' => $date,
                'period_end' => $date->copy()->endOfDay(),
                'total_bet_amount' => $totalBet,
                'total_payout' => $totalPayout,
                'total_deposit' => $totalDeposit,
                'total_withdraw' => $totalWithdraw,
                'gross_profit' => $totalBet - $totalPayout,
                'net_profit' => ($totalBet - $totalPayout) + ($totalDeposit - $totalWithdraw) * 0.01,
                'margin_percent' => round(($totalBet - $totalPayout) / max($totalBet, 1) * 100, 2),
                'active_users' => rand(15, 28),
                'new_users' => rand(0, 5),
                'total_tickets' => rand(30, 200),
                'total_wins' => rand(5, 40),
                'avg_win_rate' => rand(10, 25) / 100,
            ]);
        }
    }

    // ─────────────────────────────────────────
    // Number Exposures
    // ─────────────────────────────────────────

    private function seedNumberExposures(): void
    {
        $openRounds = collect($this->demoRounds)->where('status', RoundStatus::Open->value);
        if ($openRounds->isEmpty() || empty($this->betTypes)) return;

        $hotNumbers = ['123', '456', '789', '999', '555', '000', '69', '13', '77', '42'];
        $btList = array_values($this->betTypes);

        foreach ($openRounds->take(3) as $round) {
            foreach (array_slice($hotNumbers, 0, rand(4, 8)) as $number) {
                $bt = $btList[array_rand($btList)];
                $totalBet = rand(5000, 80000);
                $rate = $this->getRateForBetType($bt['slug']);

                NumberExposure::create([
                    'lottery_round_id' => $round['id'],
                    'bet_type_id' => $bt['id'],
                    'number' => $number,
                    'total_bet_amount' => $totalBet,
                    'bet_count' => rand(5, 50),
                    'potential_payout' => $totalBet * $rate,
                    'effective_rate' => $rate,
                    'rate_reduction_percent' => rand(0, 20),
                    'risk_level' => ['normal', 'normal', 'high', 'critical'][rand(0, 3)],
                    'is_blocked' => rand(1, 100) <= 5,
                ]);
            }
        }
    }

    // ─────────────────────────────────────────
    // Risk Alerts
    // ─────────────────────────────────────────

    private function seedRiskAlerts(): void
    {
        $alertTypes = [
            ['high_win_rate', 'อัตราชนะสูงผิดปกติ', 'ผู้ใช้มีอัตราชนะสูงเกิน 60% ใน 7 วันที่ผ่านมา'],
            ['large_bet', 'เดิมพันยอดสูง', 'พบการเดิมพันยอดสูงผิดปกติจากผู้ใช้'],
            ['suspicious_pattern', 'รูปแบบเดิมพันผิดปกติ', 'ระบบตรวจพบรูปแบบการเดิมพันที่น่าสงสัย'],
            ['high_exposure', 'ความเสี่ยงหมายเลขสูง', 'เลขบางตัวมียอดเดิมพันรวมสูง อาจส่งผลต่อกำไร'],
            ['rapid_betting', 'เดิมพันถี่ผิดปกติ', 'ผู้ใช้มีความถี่ในการเดิมพันสูงผิดปกติ'],
        ];

        $severities = ['info', 'warning', 'critical'];
        $statuses = ['new', 'new', 'new', 'acknowledged', 'resolved'];
        $activeUsers = collect($this->demoUsers)->where('status', UserStatus::Active->value);

        for ($i = 0; $i < 10; $i++) {
            $alert = $alertTypes[array_rand($alertTypes)];
            $severity = $severities[array_rand($severities)];
            $status = $statuses[array_rand($statuses)];
            $user = $activeUsers->random();

            RiskAlert::create([
                'alert_type' => $alert[0],
                'severity' => $severity,
                'user_id' => $user['id'],
                'title' => $alert[1],
                'description' => $alert[2] . ' (Demo User: ' . $user['name'] . ')',
                'status' => $status,
                'acknowledged_by' => $status !== 'new' ? $this->adminId : null,
                'acknowledged_at' => $status !== 'new' ? now()->subHours(rand(1, 48)) : null,
                'created_at' => now()->subDays(rand(0, 7)),
                'updated_at' => now(),
            ]);
        }
    }

    // ─────────────────────────────────────────
    // Affiliate Commissions
    // ─────────────────────────────────────────

    private function seedAffiliateCommissions(): void
    {
        // Users 1-10 were referred by user 0
        $referrer = $this->demoUsers[0] ?? null;
        if (!$referrer) return;

        $referredUsers = collect($this->demoUsers)->filter(fn ($u) => ($u['referred_by'] ?? null) === $referrer['id']);

        foreach ($referredUsers as $fromUser) {
            $numCommissions = rand(1, 5);
            for ($c = 0; $c < $numCommissions; $c++) {
                $betAmount = rand(100, 5000);
                $commRate = 0.5;

                AffiliateCommission::create([
                    'user_id' => $referrer['id'],
                    'from_user_id' => $fromUser['id'],
                    'bet_amount' => $betAmount,
                    'commission_rate' => $commRate,
                    'commission' => round($betAmount * $commRate / 100, 2),
                    'status' => ['pending', 'pending', 'paid'][rand(0, 2)],
                    'paid_at' => rand(0, 1) ? now()->subDays(rand(1, 10)) : null,
                    'created_at' => now()->subDays(rand(0, 13)),
                ]);
            }
        }
    }

    // ─────────────────────────────────────────
    // Messages
    // ─────────────────────────────────────────

    private function seedMessages(): void
    {
        $activeUsers = collect($this->demoUsers)->where('status', UserStatus::Active->value)->take(5);
        $msgTemplates = [
            'สอบถามเรื่องการฝากเงินครับ',
            'ถอนเงินได้เลยไหมคะ',
            'อยากสมัคร VIP ต้องทำอย่างไร',
            'แจ้งปัญหาเข้าระบบไม่ได้',
            'ขอบคุณสำหรับบริการดีๆ ครับ',
            'หวยงวดนี้เปิดรับกี่โมงคะ',
            'ฝากเงินไปแล้วยังไม่เข้าเลย',
            'อยากเปลี่ยนบัญชีธนาคาร',
        ];
        $adminResponses = [
            'ได้เลยครับ ระบบจะดำเนินการให้ภายใน 5 นาที',
            'ขอบคุณที่แจ้งครับ ทีมงานกำลังตรวจสอบ',
            'สามารถทำรายการได้เลยครับ ผ่านหน้าเว็บ',
            'เรียบร้อยแล้วครับ ลองเข้าใหม่อีกครั้ง',
        ];

        foreach ($activeUsers as $user) {
            // User sends message
            Message::create([
                'sender_id' => $user['id'],
                'receiver_id' => $this->adminId,
                'message' => $msgTemplates[array_rand($msgTemplates)],
                'is_read' => true,
                'created_at' => now()->subHours(rand(2, 48)),
            ]);

            // Admin replies
            Message::create([
                'sender_id' => $this->adminId,
                'receiver_id' => $user['id'],
                'message' => $adminResponses[array_rand($adminResponses)],
                'is_read' => rand(0, 1),
                'created_at' => now()->subHours(rand(1, 24)),
            ]);
        }
    }

    // ─────────────────────────────────────────
    // Notifications
    // ─────────────────────────────────────────

    private function seedNotifications(): void
    {
        $activeUsers = collect($this->demoUsers)->where('status', UserStatus::Active->value)->take(15);
        $templates = [
            ['type' => 'draw_reminder', 'title' => 'หวยใกล้ปิดรับ!', 'body' => 'หวยรัฐบาลจะปิดรับภายใน 30 นาที'],
            ['type' => 'result_alert', 'title' => 'ผลหวยออกแล้ว!', 'body' => 'ผลหวยงวดล่าสุดออกแล้ว คลิกเพื่อตรวจ'],
            ['type' => 'deposit_success', 'title' => 'ฝากเงินสำเร็จ', 'body' => 'ยอดเงินฝากของคุณได้รับการอนุมัติแล้ว'],
            ['type' => 'promotion', 'title' => 'โปรโมชั่นพิเศษ!', 'body' => 'รับโบนัส 10% เมื่อฝากเงินวันนี้'],
            ['type' => 'win_alert', 'title' => 'ยินดีด้วย! คุณถูกรางวัล', 'body' => 'โพยของคุณถูกรางวัล ตรวจสอบเงินรางวัลได้เลย'],
        ];

        foreach ($activeUsers as $user) {
            $numNotifs = rand(2, 5);
            for ($n = 0; $n < $numNotifs; $n++) {
                $tpl = $templates[array_rand($templates)];
                Notification::create([
                    'id' => Str::uuid()->toString(),
                    'user_id' => $user['id'],
                    'type' => $tpl['type'],
                    'title' => $tpl['title'],
                    'body' => $tpl['body'],
                    'data' => json_encode(['demo' => true]),
                    'read_at' => rand(0, 1) ? now()->subHours(rand(1, 48)) : null,
                    'created_at' => now()->subHours(rand(1, 168)),
                ]);
            }
        }
    }
}
