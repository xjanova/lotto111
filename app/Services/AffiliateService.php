<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\AffiliateCommission;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AffiliateService
{
    public function __construct(
        private BalanceService $balanceService,
    ) {}

    /**
     * Calculate and create commission for a bet
     */
    public function calculateCommission(Ticket $ticket): ?AffiliateCommission
    {
        $bettor = $ticket->user;

        if (! $bettor->referred_by) {
            return null;
        }

        $referrer = User::find($bettor->referred_by);
        if (! $referrer || ! $referrer->isActive()) {
            return null;
        }

        $commissionRate = config('lottery.affiliate_commission_rate', 0.5);
        $betAmount = (float) ($ticket->total_amount ?: $ticket->amount);
        $commission = $betAmount * ($commissionRate / 100);

        if ($commission < 0.01) {
            return null;
        }

        return AffiliateCommission::create([
            'user_id' => $referrer->id,
            'from_user_id' => $bettor->id,
            'ticket_id' => $ticket->id,
            'bet_amount' => $betAmount,
            'commission_rate' => $commissionRate,
            'commission' => $commission,
            'status' => 'pending',
        ]);
    }

    /**
     * Get affiliate dashboard data
     */
    public function getDashboard(User $user): array
    {
        $referrals = User::where('referred_by', $user->id)->count();

        $totalCommission = AffiliateCommission::where('user_id', $user->id)->sum('commission');
        $pendingCommission = AffiliateCommission::where('user_id', $user->id)->where('status', 'pending')->sum('commission');
        $paidCommission = AffiliateCommission::where('user_id', $user->id)->where('status', 'paid')->sum('commission');

        $todayCommission = AffiliateCommission::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->sum('commission');

        return [
            'referral_code' => $user->referral_code,
            'referral_link' => url("/register?ref={$user->referral_code}"),
            'total_referrals' => $referrals,
            'total_commission' => $totalCommission,
            'pending_commission' => $pendingCommission,
            'paid_commission' => $paidCommission,
            'today_commission' => $todayCommission,
            'commission_rate' => config('lottery.affiliate_commission_rate', 0.5),
        ];
    }

    /**
     * Get referred members list
     */
    public function getReferredMembers(User $user, int $limit = 20): mixed
    {
        return User::where('referred_by', $user->id)
            ->select('id', 'name', 'phone', 'created_at', 'last_login_at')
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }

    /**
     * Get commission history
     */
    public function getCommissions(User $user, int $limit = 20): mixed
    {
        return AffiliateCommission::where('user_id', $user->id)
            ->with(['fromUser:id,name,phone', 'ticket:id,ticket_code,total_amount'])
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }

    /**
     * Withdraw pending commissions
     */
    public function withdrawCommissions(User $user): array
    {
        $pendingAmount = AffiliateCommission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('commission');

        if ($pendingAmount < 1) {
            return ['success' => false, 'message' => 'ยอดคอมมิชชั่นไม่เพียงพอ'];
        }

        return DB::transaction(function () use ($user, $pendingAmount) {
            AffiliateCommission::where('user_id', $user->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

            $this->balanceService->credit(
                $user,
                $pendingAmount,
                'ถอนคอมมิชชั่น Affiliate',
                TransactionType::Commission,
            );

            return [
                'success' => true,
                'message' => 'ถอนคอมมิชชั่นสำเร็จ',
                'amount' => $pendingAmount,
            ];
        });
    }

    /**
     * Get daily commission report
     */
    public function getDailyReport(User $user, int $days = 30): mixed
    {
        return AffiliateCommission::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, SUM(bet_amount) as total_bet, SUM(commission) as total_commission, COUNT(*) as total_bets')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
    }
}
