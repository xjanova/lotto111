<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Enums\VipLevel;
use App\Models\User;
use App\Models\UserGamification;
use App\Models\Mission;
use App\Models\UserMission;
use App\Models\SpinReward;
use App\Models\UserSpinHistory;
use App\Models\Badge;
use App\Models\UserBadge;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    /**
     * Award XP to user and check for level up
     */
    public function awardXp(User $user, int $xp, string $reason = ''): void
    {
        $gamification = $user->gamification()->firstOrCreate(['user_id' => $user->id]);

        $oldLevel = VipLevel::fromXp($gamification->xp);
        $gamification->increment('xp', $xp);
        $newLevel = VipLevel::fromXp($gamification->xp + $xp);

        if ($oldLevel !== $newLevel) {
            $this->handleLevelUp($user, $oldLevel, $newLevel);
        }
    }

    /**
     * Process daily login streak
     */
    public function processLoginStreak(User $user): array
    {
        $gamification = $user->gamification()->firstOrCreate(['user_id' => $user->id]);
        $today = now()->toDateString();

        if ($gamification->last_daily_claim === $today) {
            return ['already_claimed' => true, 'streak' => $gamification->login_streak];
        }

        $yesterday = now()->subDay()->toDateString();
        $isConsecutive = $gamification->last_daily_claim === $yesterday;

        $gamification->update([
            'login_streak' => $isConsecutive ? $gamification->login_streak + 1 : 1,
            'longest_streak' => max($gamification->longest_streak, $gamification->login_streak + 1),
            'last_daily_claim' => $today,
        ]);

        $this->awardXp($user, 10, 'daily_login');

        return [
            'already_claimed' => false,
            'streak' => $gamification->login_streak,
            'xp_earned' => 10,
        ];
    }

    /**
     * Perform lucky spin
     */
    public function spin(User $user): ?SpinReward
    {
        $gamification = $user->gamification;

        if (!$gamification || $gamification->spin_count <= 0) {
            return null;
        }

        // Weighted random selection
        $rewards = SpinReward::where('is_active', true)->get();
        $reward = $this->weightedRandom($rewards);

        if ($reward) {
            DB::transaction(function () use ($user, $gamification, $reward) {
                $gamification->decrement('spin_count');

                UserSpinHistory::create([
                    'user_id' => $user->id,
                    'spin_reward_id' => $reward->id,
                ]);

                $this->applySpinReward($user, $reward);
            });
        }

        return $reward;
    }

    /**
     * Update mission progress
     */
    public function updateMissionProgress(User $user, string $conditionType, float $value = 1): void
    {
        $today = now()->toDateString();

        $activeMissions = Mission::where('is_active', true)
            ->where('condition_type', $conditionType)
            ->where(function ($q) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', now());
            })
            ->get();

        foreach ($activeMissions as $mission) {
            $periodDate = match ($mission->type->value) {
                'daily' => $today,
                'weekly' => now()->startOfWeek()->toDateString(),
                default => '1970-01-01',
            };

            $userMission = UserMission::firstOrCreate([
                'user_id' => $user->id,
                'mission_id' => $mission->id,
                'period_date' => $periodDate,
            ]);

            if (!$userMission->is_completed) {
                $userMission->increment('progress', $value);

                if ($userMission->progress >= $mission->condition_value) {
                    $userMission->update([
                        'is_completed' => true,
                        'completed_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Claim mission reward
     */
    public function claimMissionReward(User $user, UserMission $userMission): bool
    {
        if (!$userMission->is_completed || $userMission->is_claimed) {
            return false;
        }

        $mission = $userMission->mission;

        DB::transaction(function () use ($user, $userMission, $mission) {
            $userMission->update([
                'is_claimed' => true,
                'claimed_at' => now(),
            ]);

            if ($mission->reward_xp > 0) {
                $this->awardXp($user, $mission->reward_xp, "mission:{$mission->id}");
            }

            if ($mission->reward_credit > 0) {
                app(BalanceService::class)->credit($user, $mission->reward_credit, "Mission reward: {$mission->title}", TransactionType::Bonus);
            }

            if ($mission->reward_spins > 0) {
                $user->gamification->increment('spin_count', $mission->reward_spins);
            }

            if ($mission->reward_badge_id) {
                $this->awardBadge($user, $mission->reward_badge_id);
            }
        });

        return true;
    }

    /**
     * Award a badge to user
     */
    public function awardBadge(User $user, int $badgeId): bool
    {
        $exists = UserBadge::where('user_id', $user->id)->where('badge_id', $badgeId)->exists();

        if (!$exists) {
            UserBadge::create([
                'user_id' => $user->id,
                'badge_id' => $badgeId,
            ]);
            return true;
        }

        return false;
    }

    /**
     * Handle level up event
     */
    private function handleLevelUp(User $user, VipLevel $oldLevel, VipLevel $newLevel): void
    {
        // Send notification
        // Award bonus
        // Update user vip_level
        event(new \App\Events\UserLevelUp($user, $oldLevel, $newLevel));
    }

    /**
     * Weighted random selection
     */
    private function weightedRandom($items): ?SpinReward
    {
        $totalWeight = $items->sum('probability');
        $random = mt_rand(0, (int) ($totalWeight * 10000)) / 10000;

        $cumulative = 0;
        foreach ($items as $item) {
            $cumulative += $item->probability;
            if ($random <= $cumulative) {
                return $item;
            }
        }

        return $items->last();
    }

    /**
     * Apply spin reward to user
     */
    private function applySpinReward(User $user, SpinReward $reward): void
    {
        match ($reward->type) {
            'xp' => $this->awardXp($user, (int) $reward->value, 'spin_reward'),
            'credit' => app(BalanceService::class)->credit($user, $reward->value, 'Lucky Spin Reward', TransactionType::Bonus),
            'badge' => $this->awardBadge($user, (int) $reward->value),
            default => null,
        };
    }
}
