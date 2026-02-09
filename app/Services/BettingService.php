<?php

namespace App\Services;

use App\Enums\RoundStatus;
use App\Enums\TicketStatus;
use App\Enums\TransactionType;
use App\Events\BetPlaced;
use App\Models\BetLimit;
use App\Models\BetType;
use App\Models\BetTypeRate;
use App\Models\LotteryRound;
use App\Models\NumberExposure;
use App\Models\Ticket;
use App\Models\TicketItem;
use App\Models\User;
use App\Services\Risk\RiskEngineService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BettingService
{
    public function __construct(
        private BalanceService $balanceService,
        private RiskEngineService $riskEngine,
    ) {}

    /**
     * Place a bet (create ticket with items)
     *
     * @param array $bets [{bet_type_id, number, amount}, ...]
     */
    public function placeBet(User $user, int $roundId, array $bets): array
    {
        $round = LotteryRound::findOrFail($roundId);

        // Validate round is open
        if ($round->status !== RoundStatus::Open) {
            return ['success' => false, 'message' => 'รอบนี้ปิดรับแทงแล้ว'];
        }

        if ($round->close_at->isPast()) {
            return ['success' => false, 'message' => 'หมดเวลารับแทงแล้ว'];
        }

        // Validate bet count
        if (count($bets) > config('lottery.max_items_per_ticket', 100)) {
            return ['success' => false, 'message' => 'จำนวนรายการแทงเกินกำหนด'];
        }

        // Validate and calculate
        $validatedBets = [];
        $totalAmount = 0;

        foreach ($bets as $bet) {
            $validation = $this->validateSingleBet($user, $round, $bet);
            if (! $validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }
            $validatedBets[] = $validation['bet'];
            $totalAmount += $validation['bet']['amount'];
        }

        // Check balance
        if (! $user->hasBalance($totalAmount)) {
            return ['success' => false, 'message' => 'ยอดเงินไม่เพียงพอ'];
        }

        // Check user risk limits
        $riskProfile = $user->riskProfile;
        if ($riskProfile && $riskProfile->max_bet_per_ticket && $totalAmount > (float) $riskProfile->max_bet_per_ticket) {
            return ['success' => false, 'message' => 'ยอดแทงเกินลิมิตต่อโพย'];
        }

        return DB::transaction(function () use ($user, $round, $validatedBets, $totalAmount) {
            // Create ticket
            $ticketCode = config('lottery.ticket_code_prefix', 'TK')
                . now()->format('ymd')
                . strtoupper(Str::random(6));

            $ticket = Ticket::create([
                'user_id' => $user->id,
                'lottery_round_id' => $round->id,
                'ticket_code' => $ticketCode,
                'total_amount' => $totalAmount,
                'status' => TicketStatus::Pending,
                'bet_at' => now(),
                // Keep legacy fields populated for the first bet
                'bet_type_id' => $validatedBets[0]['bet_type_id'],
                'number' => $validatedBets[0]['number'],
                'amount' => $validatedBets[0]['amount'],
                'rate' => $validatedBets[0]['rate'],
            ]);

            // Create ticket items
            foreach ($validatedBets as $bet) {
                TicketItem::create([
                    'ticket_id' => $ticket->id,
                    'bet_type_id' => $bet['bet_type_id'],
                    'number' => $bet['number'],
                    'amount' => $bet['amount'],
                    'rate' => $bet['rate'],
                ]);

                // Update number exposure
                $this->updateExposure($round->id, $bet);
            }

            // Deduct balance
            $this->balanceService->debit(
                $user,
                $totalAmount,
                "แทงหวย โพย {$ticketCode}",
                TransactionType::Bet,
                $ticket->id,
                'ticket',
            );

            // Update risk profile
            $this->riskEngine->recordBet($user, $totalAmount);

            // Fire event
            BetPlaced::dispatch($ticket);

            return [
                'success' => true,
                'message' => 'ส่งโพยสำเร็จ',
                'ticket' => $ticket->load('items.betType'),
            ];
        });
    }

    /**
     * Validate a single bet entry
     */
    private function validateSingleBet(User $user, LotteryRound $round, array $bet): array
    {
        $betType = BetType::find($bet['bet_type_id'] ?? 0);
        if (! $betType || ! $betType->is_active) {
            return ['valid' => false, 'message' => 'ประเภทการแทงไม่ถูกต้อง'];
        }

        $number = $bet['number'] ?? '';
        $amount = (float) ($bet['amount'] ?? 0);

        // Validate number length
        if (strlen($number) !== $betType->digit_count) {
            return ['valid' => false, 'message' => "เลข {$number} ไม่ตรงกับประเภท {$betType->name}"];
        }

        // Validate number is numeric
        if (! ctype_digit($number)) {
            return ['valid' => false, 'message' => 'เลขต้องเป็นตัวเลขเท่านั้น'];
        }

        // Get rate
        $rate = BetTypeRate::where('lottery_type_id', $round->lottery_type_id)
            ->where('bet_type_id', $betType->id)
            ->where('is_active', true)
            ->first();

        if (! $rate) {
            return ['valid' => false, 'message' => "ไม่พบอัตราจ่ายสำหรับ {$betType->name}"];
        }

        // Validate amount range
        if ($amount < (float) $rate->min_amount || $amount > (float) $rate->max_amount) {
            return ['valid' => false, 'message' => "จำนวนเงินต้องอยู่ระหว่าง {$rate->min_amount} - {$rate->max_amount}"];
        }

        // Check bet limit (blocked numbers)
        $limit = BetLimit::where('lottery_round_id', $round->id)
            ->where('bet_type_id', $betType->id)
            ->where('number', $number)
            ->first();

        if ($limit && $limit->isBlocked()) {
            return ['valid' => false, 'message' => "เลข {$number} ถูกอั้น (ไม่รับแทง)"];
        }

        if ($limit && $amount > (float) $limit->max_amount) {
            return ['valid' => false, 'message' => "เลข {$number} แทงได้สูงสุด {$limit->max_amount} บาท"];
        }

        // Check user-specific blocked numbers
        $riskProfile = $user->riskProfile;
        if ($riskProfile) {
            $blockedNumbers = $riskProfile->blocked_numbers ? explode(',', $riskProfile->blocked_numbers) : [];
            if (in_array($number, $blockedNumbers)) {
                return ['valid' => false, 'message' => "เลข {$number} ถูกจำกัดสำหรับบัญชีนี้"];
            }

            if ($riskProfile->max_bet_per_number && $amount > (float) $riskProfile->max_bet_per_number) {
                return ['valid' => false, 'message' => "แทงได้สูงสุด {$riskProfile->max_bet_per_number} บาทต่อเลข"];
            }
        }

        // Apply risk-based rate adjustment
        $effectiveRate = (float) $rate->rate;
        if ($riskProfile && (float) $riskProfile->rate_adjustment_percent !== 0.0) {
            $effectiveRate = $effectiveRate * (1 + (float) $riskProfile->rate_adjustment_percent / 100);
        }

        return [
            'valid' => true,
            'bet' => [
                'bet_type_id' => $betType->id,
                'number' => $number,
                'amount' => $amount,
                'rate' => $effectiveRate,
            ],
        ];
    }

    /**
     * Update number exposure tracking
     */
    private function updateExposure(int $roundId, array $bet): void
    {
        NumberExposure::updateOrCreate(
            [
                'lottery_round_id' => $roundId,
                'bet_type_id' => $bet['bet_type_id'],
                'number' => $bet['number'],
            ],
            [
                'total_bet_amount' => DB::raw('total_bet_amount + ' . (float) $bet['amount']),
                'bet_count' => DB::raw('bet_count + 1'),
                'potential_payout' => DB::raw('potential_payout + ' . ((float) $bet['amount'] * (float) $bet['rate'])),
                'effective_rate' => $bet['rate'],
            ]
        );
    }

    /**
     * Cancel a pending ticket
     */
    public function cancelTicket(Ticket $ticket): array
    {
        if ($ticket->status !== TicketStatus::Pending) {
            return ['success' => false, 'message' => 'ไม่สามารถยกเลิกโพยนี้ได้'];
        }

        $round = $ticket->round;
        if ($round->status !== RoundStatus::Open) {
            return ['success' => false, 'message' => 'ไม่สามารถยกเลิกได้ รอบปิดรับแทงแล้ว'];
        }

        return DB::transaction(function () use ($ticket) {
            $ticket->update(['status' => TicketStatus::Cancelled]);

            // Refund balance
            $this->balanceService->credit(
                $ticket->user,
                (float) $ticket->total_amount ?: (float) $ticket->amount,
                "คืนเงินยกเลิกโพย {$ticket->ticket_code}",
                TransactionType::Refund,
                $ticket->id,
                'ticket',
            );

            return ['success' => true, 'message' => 'ยกเลิกโพยสำเร็จ'];
        });
    }

    /**
     * Reuse a previous ticket (copy bets to a new round)
     */
    public function reuseTicket(User $user, Ticket $ticket, int $newRoundId): array
    {
        $bets = $ticket->items->map(fn ($item) => [
            'bet_type_id' => $item->bet_type_id,
            'number' => $item->number,
            'amount' => (float) $item->amount,
        ])->toArray();

        // If no items, use the legacy single bet fields
        if (empty($bets)) {
            $bets = [[
                'bet_type_id' => $ticket->bet_type_id,
                'number' => $ticket->number,
                'amount' => (float) $ticket->amount,
            ]];
        }

        return $this->placeBet($user, $newRoundId, $bets);
    }
}
