<?php

namespace App\Services\AI;

use App\Models\LotteryType;
use App\Models\LotteryResult;
use App\Models\NumberAnalytic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class NumberAnalysisService
{
    /**
     * Get hot numbers (frequently drawn numbers)
     */
    public function getHotNumbers(LotteryType $type, string $position = 'three_top', int $limit = 10): Collection
    {
        $cacheKey = "hot_numbers:{$type->id}:{$position}";

        return Cache::remember($cacheKey, 3600, function () use ($type, $position, $limit) {
            return NumberAnalytic::where('lottery_type_id', $type->id)
                ->where('digit_position', $position)
                ->where('is_hot', true)
                ->orderByDesc('frequency')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get cold numbers (numbers not drawn for a long time)
     */
    public function getColdNumbers(LotteryType $type, string $position = 'three_top', int $limit = 10): Collection
    {
        $cacheKey = "cold_numbers:{$type->id}:{$position}";

        return Cache::remember($cacheKey, 3600, function () use ($type, $position, $limit) {
            return NumberAnalytic::where('lottery_type_id', $type->id)
                ->where('digit_position', $position)
                ->where('is_cold', true)
                ->orderByDesc('gap_count')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get frequency distribution for a lottery type
     */
    public function getFrequencyDistribution(LotteryType $type, string $position, int $lastRounds = 100): array
    {
        $results = LotteryResult::whereHas('lotteryRound', function ($q) use ($type) {
            $q->where('lottery_type_id', $type->id);
        })
            ->where('result_type', $position)
            ->latest()
            ->limit($lastRounds)
            ->pluck('result_value');

        $frequency = [];
        foreach ($results as $number) {
            $frequency[$number] = ($frequency[$number] ?? 0) + 1;
        }

        arsort($frequency);
        return $frequency;
    }

    /**
     * AI Smart Pick - generate suggested numbers
     */
    public function smartPick(LotteryType $type, string $position = 'three_top', int $count = 5): array
    {
        $hot = $this->getHotNumbers($type, $position, 20);
        $cold = $this->getColdNumbers($type, $position, 20);
        $frequency = $this->getFrequencyDistribution($type, $position);

        // Mix strategy: 60% frequency-based, 20% hot, 20% cold (overdue)
        $suggestions = [];

        // Frequency-based picks
        $frequencyPicks = array_slice(array_keys($frequency), 0, (int) ceil($count * 0.6));
        $suggestions = array_merge($suggestions, $frequencyPicks);

        // Cold (overdue) picks
        $coldPicks = $cold->take((int) ceil($count * 0.2))->pluck('number')->toArray();
        $suggestions = array_merge($suggestions, $coldPicks);

        // Hot picks
        $hotPicks = $hot->take((int) ceil($count * 0.2))->pluck('number')->toArray();
        $suggestions = array_merge($suggestions, $hotPicks);

        // Remove duplicates and limit
        $suggestions = array_unique($suggestions);
        $suggestions = array_slice($suggestions, 0, $count);

        return [
            'numbers' => $suggestions,
            'confidence' => $this->calculateConfidence($suggestions, $frequency),
            'strategy' => 'mixed_frequency_overdue',
        ];
    }

    /**
     * "What If" Simulator - simulate past results
     */
    public function whatIfSimulator(LotteryType $type, string $number, string $betType, float $amount, int $lastRounds = 10): array
    {
        $results = LotteryResult::whereHas('lotteryRound', function ($q) use ($type) {
            $q->where('lottery_type_id', $type->id)
                ->where('status', 'resulted');
        })
            ->where('result_type', $betType)
            ->latest()
            ->limit($lastRounds)
            ->get();

        $totalBet = $amount * $lastRounds;
        $totalWin = 0;
        $wins = 0;
        $timeline = [];

        foreach ($results as $result) {
            $isWon = $result->result_value === $number;
            $winAmount = $isWon ? $amount * $this->getRate($type, $betType) : 0;
            $totalWin += $winAmount;

            if ($isWon) $wins++;

            $timeline[] = [
                'round' => $result->lotteryRound->round_code ?? '',
                'result' => $result->result_value,
                'bet' => $number,
                'amount' => $amount,
                'won' => $isWon,
                'win_amount' => $winAmount,
            ];
        }

        return [
            'total_rounds' => $lastRounds,
            'total_bet' => $totalBet,
            'total_win' => $totalWin,
            'net_profit' => $totalWin - $totalBet,
            'win_count' => $wins,
            'win_rate' => $lastRounds > 0 ? round(($wins / $lastRounds) * 100, 2) : 0,
            'timeline' => $timeline,
        ];
    }

    /**
     * Recalculate analytics after new results
     */
    public function recalculateAnalytics(LotteryType $type): void
    {
        $positions = ['three_top', 'three_tod', 'two_top', 'two_bottom'];

        foreach ($positions as $position) {
            $results = LotteryResult::whereHas('lotteryRound', function ($q) use ($type) {
                $q->where('lottery_type_id', $type->id);
            })
                ->where('result_type', $position)
                ->latest()
                ->limit(200)
                ->get();

            $frequency = [];
            $lastAppeared = [];

            foreach ($results as $index => $result) {
                $num = $result->result_value;
                $frequency[$num] = ($frequency[$num] ?? 0) + 1;

                if (!isset($lastAppeared[$num])) {
                    $lastAppeared[$num] = $index;
                }
            }

            $avgFreq = count($frequency) > 0 ? array_sum($frequency) / count($frequency) : 0;

            foreach ($frequency as $number => $freq) {
                $gap = $lastAppeared[$number] ?? 0;

                NumberAnalytic::updateOrCreate(
                    [
                        'lottery_type_id' => $type->id,
                        'number' => $number,
                        'digit_position' => $position,
                    ],
                    [
                        'frequency' => $freq,
                        'gap_count' => $gap,
                        'avg_gap' => $avgFreq > 0 ? round(count($results) / $freq, 2) : 0,
                        'is_hot' => $freq > $avgFreq * 1.3,
                        'is_cold' => $gap > ($avgFreq > 0 ? round(count($results) / $avgFreq * 2) : 20),
                    ]
                );
            }
        }

        // Clear cache
        Cache::tags(["analytics:{$type->id}"])->flush();
    }

    private function calculateConfidence(array $numbers, array $frequency): float
    {
        if (empty($numbers) || empty($frequency)) return 0;

        $maxFreq = max($frequency);
        $totalConfidence = 0;

        foreach ($numbers as $num) {
            $freq = $frequency[$num] ?? 0;
            $totalConfidence += $maxFreq > 0 ? ($freq / $maxFreq) * 100 : 0;
        }

        return round($totalConfidence / count($numbers), 1);
    }

    private function getRate(LotteryType $type, string $betType): float
    {
        return Cache::remember("rate:{$type->id}:{$betType}", 3600, function () use ($type, $betType) {
            $rate = $type->betTypeRates()
                ->whereHas('betType', fn($q) => $q->where('slug', $betType))
                ->first();

            return $rate ? $rate->rate : 0;
        });
    }
}
