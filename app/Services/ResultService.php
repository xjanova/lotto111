<?php

namespace App\Services;

use App\Enums\RoundStatus;
use App\Enums\TicketStatus;
use App\Enums\TransactionType;
use App\Events\ResultAnnounced;
use App\Models\LotteryResult;
use App\Models\LotteryRound;
use App\Models\Ticket;
use App\Models\TicketItem;
use App\Services\Risk\RiskEngineService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResultService
{
    public function __construct(
        private BalanceService $balanceService,
        private RiskEngineService $riskEngine,
    ) {}

    /**
     * Submit results for a round and process all tickets
     *
     * @param array $results [{result_type => result_value}, ...]
     */
    public function submitResults(LotteryRound $round, array $results): array
    {
        if ($round->status === RoundStatus::Resulted) {
            return ['success' => false, 'message' => 'รอบนี้ออกผลแล้ว'];
        }

        if ($round->status === RoundStatus::Open) {
            $round->update(['status' => RoundStatus::Closed]);
        }

        return DB::transaction(function () use ($round, $results) {
            // Save results
            foreach ($results as $type => $value) {
                LotteryResult::updateOrCreate(
                    ['lottery_round_id' => $round->id, 'result_type' => $type],
                    ['result_value' => $value]
                );
            }

            $round->update([
                'status' => RoundStatus::Resulted,
                'result_at' => now(),
            ]);

            // Process all pending tickets for this round
            $stats = $this->processTickets($round, $results);

            // Fire event
            ResultAnnounced::dispatch($round, $results);

            // Clear cache
            Cache::forget("results:{$round->result_at?->format('Y-m-d')}");

            return [
                'success' => true,
                'message' => 'ออกผลรางวัลสำเร็จ',
                'stats' => $stats,
            ];
        });
    }

    /**
     * Process all tickets for a resulted round
     */
    private function processTickets(LotteryRound $round, array $results): array
    {
        $totalWinners = 0;
        $totalLosers = 0;
        $totalPayout = 0;
        $totalBet = 0;

        $tickets = Ticket::where('lottery_round_id', $round->id)
            ->where('status', TicketStatus::Pending)
            ->with(['items.betType', 'user'])
            ->get();

        foreach ($tickets as $ticket) {
            $ticketResult = $this->processTicket($ticket, $results);
            $totalBet += (float) ($ticket->total_amount ?: $ticket->amount);

            if ($ticketResult['total_win'] > 0) {
                $totalWinners++;
                $totalPayout += $ticketResult['total_win'];
            } else {
                $totalLosers++;
            }
        }

        return [
            'total_tickets' => $tickets->count(),
            'winners' => $totalWinners,
            'losers' => $totalLosers,
            'total_bet' => $totalBet,
            'total_payout' => $totalPayout,
            'profit' => $totalBet - $totalPayout,
        ];
    }

    /**
     * Process a single ticket against results
     */
    private function processTicket(Ticket $ticket, array $results): array
    {
        $totalWin = 0;

        // Process ticket items
        $items = $ticket->items;

        if ($items->isEmpty()) {
            // Legacy single-bet ticket
            $won = $this->checkWin($ticket->betType?->slug, $ticket->number, $results);
            if ($won) {
                $winAmount = (float) $ticket->amount * (float) $ticket->rate;
                $totalWin = $winAmount;
                $ticket->update([
                    'win_amount' => $winAmount,
                    'status' => TicketStatus::Won,
                    'result_at' => now(),
                ]);
            } else {
                $ticket->update([
                    'status' => TicketStatus::Lost,
                    'result_at' => now(),
                ]);
            }
        } else {
            // Multi-item ticket
            foreach ($items as $item) {
                $won = $this->checkWin($item->betType?->slug, $item->number, $results);
                $winAmount = $won ? (float) $item->amount * (float) $item->rate : 0;

                $item->update([
                    'is_won' => $won,
                    'win_amount' => $winAmount,
                ]);

                $totalWin += $winAmount;
            }

            $ticket->update([
                'total_win' => $totalWin,
                'status' => $totalWin > 0 ? TicketStatus::Won : TicketStatus::Lost,
                'result_at' => now(),
            ]);
        }

        // Credit winnings
        if ($totalWin > 0) {
            $this->balanceService->credit(
                $ticket->user,
                $totalWin,
                "ถูกรางวัล โพย {$ticket->ticket_code}",
                TransactionType::Win,
                $ticket->id,
                'ticket',
            );

            $this->riskEngine->recordWin($ticket->user, $totalWin);
        }

        return ['total_win' => $totalWin];
    }

    /**
     * Check if a number wins against results
     */
    private function checkWin(string $betTypeSlug, string $number, array $results): bool
    {
        return match ($betTypeSlug) {
            'three_top' => ($results['three_top'] ?? '') === $number,
            'three_tod' => $this->checkTod($number, $results['three_top'] ?? ''),
            'three_bottom' => ($results['two_bottom'] ?? null) !== null && str_ends_with($results['two_bottom'], '') && ($results['three_bottom'] ?? '') === $number,
            'two_top' => ($results['three_top'] ?? '') !== '' && substr($results['three_top'], -2) === $number,
            'two_bottom' => ($results['two_bottom'] ?? '') === $number,
            'two_tod' => $this->checkTod($number, substr($results['three_top'] ?? '', -2)),
            'run_top' => $this->checkRun($number, $results['three_top'] ?? ''),
            'run_bottom' => $this->checkRun($number, $results['two_bottom'] ?? ''),
            'four_top' => ($results['four_top'] ?? '') === $number,
            'four_tod' => $this->checkTod($number, $results['four_top'] ?? ''),
            'five_tod' => $this->checkTod($number, $results['five_top'] ?? ''),
            default => false,
        };
    }

    /**
     * Check "tod" (any permutation) win
     */
    private function checkTod(string $number, string $result): bool
    {
        if (strlen($number) !== strlen($result) || empty($result)) {
            return false;
        }
        $numberChars = str_split($number);
        $resultChars = str_split($result);
        sort($numberChars);
        sort($resultChars);

        return $numberChars === $resultChars && $number !== $result;
    }

    /**
     * Check "run" (single digit appears in result) win
     */
    private function checkRun(string $number, string $result): bool
    {
        if (strlen($number) !== 1 || empty($result)) {
            return false;
        }
        return str_contains($result, $number);
    }

    /**
     * Get results for a specific date
     */
    public function getResultsByDate(string $date): mixed
    {
        return Cache::remember("results:{$date}", 86400, function () use ($date) {
            return LotteryRound::with(['lotteryType', 'results'])
                ->where('status', RoundStatus::Resulted)
                ->whereDate('result_at', $date)
                ->orderBy('result_at', 'desc')
                ->get();
        });
    }

    /**
     * Get results by lottery type
     */
    public function getResultsByType(int $lotteryTypeId, int $limit = 20): mixed
    {
        return LotteryRound::with('results')
            ->where('lottery_type_id', $lotteryTypeId)
            ->where('status', RoundStatus::Resulted)
            ->orderBy('result_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get latest results across all lottery types
     */
    public function getLatestResults(): mixed
    {
        return LotteryRound::with(['lotteryType', 'results'])
            ->where('status', RoundStatus::Resulted)
            ->orderBy('result_at', 'desc')
            ->limit(50)
            ->get()
            ->groupBy('lottery_type_id');
    }
}
