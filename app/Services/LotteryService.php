<?php

namespace App\Services;

use App\Enums\LotteryCategory;
use App\Enums\RoundStatus;
use App\Models\BetType;
use App\Models\BetTypeRate;
use App\Models\LotteryRound;
use App\Models\LotteryType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LotteryService
{
    /**
     * Get all active lottery types grouped by category
     */
    public function getActiveTypes(): array
    {
        return Cache::remember('lottery:types:active', 3600, function () {
            return LotteryType::active()
                ->orderBy('sort_order')
                ->get()
                ->groupBy(fn ($type) => $type->category->value)
                ->toArray();
        });
    }

    /**
     * Get currently open rounds
     */
    public function getOpenRounds(?int $lotteryTypeId = null): mixed
    {
        $query = LotteryRound::with('lotteryType')
            ->where('status', RoundStatus::Open)
            ->where('close_at', '>', now())
            ->orderBy('close_at');

        if ($lotteryTypeId) {
            $query->where('lottery_type_id', $lotteryTypeId);
        }

        return $query->get();
    }

    /**
     * Get rates for a lottery type
     */
    public function getRates(int $lotteryTypeId): mixed
    {
        return Cache::remember("lottery:rates:{$lotteryTypeId}", 3600, function () use ($lotteryTypeId) {
            return BetTypeRate::with('betType')
                ->where('lottery_type_id', $lotteryTypeId)
                ->where('is_active', true)
                ->get();
        });
    }

    /**
     * Create a new round
     */
    public function createRound(
        int $lotteryTypeId,
        Carbon $openAt,
        Carbon $closeAt,
        ?int $roundNumber = null,
    ): LotteryRound {
        $type = LotteryType::findOrFail($lotteryTypeId);

        $roundCode = strtoupper(Str::substr($type->slug, 0, 3))
            . '-' . $closeAt->format('YmdHi')
            . '-' . Str::random(4);

        return LotteryRound::create([
            'lottery_type_id' => $lotteryTypeId,
            'round_code' => $roundCode,
            'round_number' => $roundNumber,
            'status' => $openAt->isFuture() ? RoundStatus::Upcoming : RoundStatus::Open,
            'open_at' => $openAt,
            'close_at' => $closeAt,
        ]);
    }

    /**
     * Open a round for betting
     */
    public function openRound(LotteryRound $round): void
    {
        $round->update(['status' => RoundStatus::Open]);
        Cache::forget('lottery:rounds:active');
    }

    /**
     * Close a round (no more bets)
     */
    public function closeRound(LotteryRound $round): void
    {
        $round->update(['status' => RoundStatus::Closed]);
        Cache::forget('lottery:rounds:active');
    }

    /**
     * Auto-open and auto-close rounds based on schedule
     */
    public function processRoundSchedule(): void
    {
        // Open rounds that should be open
        LotteryRound::where('status', RoundStatus::Upcoming)
            ->where('open_at', '<=', now())
            ->each(fn ($round) => $this->openRound($round));

        // Close rounds that should be closed
        LotteryRound::where('status', RoundStatus::Open)
            ->where('close_at', '<=', now())
            ->each(fn ($round) => $this->closeRound($round));
    }

    /**
     * Generate Yeekee rounds for today
     */
    public function generateYeekeeRounds(): int
    {
        $yeekeeType = LotteryType::where('slug', 'yeekee')->first();
        if (! $yeekeeType) {
            return 0;
        }

        $roundsPerDay = config('lottery.yeekee.rounds_per_day', 144);
        $interval = config('lottery.yeekee.interval_minutes', 10);
        $today = today();
        $created = 0;

        for ($i = 1; $i <= $roundsPerDay; $i++) {
            $openAt = $today->copy()->addMinutes(($i - 1) * $interval);
            $closeAt = $openAt->copy()->addMinutes($interval - 1);

            $roundCode = 'YK-' . $today->format('Ymd') . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);

            if (LotteryRound::where('round_code', $roundCode)->exists()) {
                continue;
            }

            LotteryRound::create([
                'lottery_type_id' => $yeekeeType->id,
                'round_code' => $roundCode,
                'round_number' => $i,
                'status' => $openAt->isPast() && $closeAt->isFuture() ? RoundStatus::Open : ($openAt->isPast() ? RoundStatus::Closed : RoundStatus::Upcoming),
                'open_at' => $openAt,
                'close_at' => $closeAt,
            ]);
            $created++;
        }

        return $created;
    }

    /**
     * Get round details with results
     */
    public function getRoundDetail(int $roundId): ?LotteryRound
    {
        return LotteryRound::with(['lotteryType', 'results', 'betLimits.betType'])
            ->find($roundId);
    }

    /**
     * Clear lottery cache
     */
    public function clearCache(): void
    {
        Cache::forget('lottery:types:active');
        Cache::forget('lottery:rounds:active');

        LotteryType::all()->each(function ($type) {
            Cache::forget("lottery:rates:{$type->id}");
        });
    }
}
