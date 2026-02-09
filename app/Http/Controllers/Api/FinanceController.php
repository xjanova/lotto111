<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\WithdrawRequest;
use App\Services\AffiliateService;
use App\Services\BalanceService;
use App\Services\WithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function __construct(
        private BalanceService $balanceService,
        private WithdrawalService $withdrawalService,
        private AffiliateService $affiliateService,
    ) {}

    /**
     * GET /api/user/balance
     */
    public function balance(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => (float) $user->balance,
                'vip_level' => $user->vip_level?->value,
                'xp' => $user->xp,
            ],
        ]);
    }

    /**
     * POST /api/withdrawals
     */
    public function withdraw(WithdrawRequest $request): JsonResponse
    {
        $result = $this->withdrawalService->create(
            $request->user(),
            $request->amount,
            $request->bank_account_id,
        );

        return response()->json($result, $result['success'] ? 201 : 422);
    }

    /**
     * GET /api/withdrawals
     */
    public function withdrawals(Request $request): JsonResponse
    {
        $data = $this->withdrawalService->getHistory($request->user());

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * GET /api/transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $data = $this->balanceService->getTransactions(
            $request->user(),
            $request->string('type')->value() ?: null,
            $request->integer('per_page', 20),
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * GET /api/financial-report
     */
    public function financialReport(Request $request): JsonResponse
    {
        $user = $request->user();
        $from = $request->date('from', now()->subDays(30));
        $to = $request->date('to', now());

        $deposits = $user->transactions()
            ->where('type', 'deposit')
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        $withdrawals = abs($user->transactions()
            ->where('type', 'withdraw')
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount'));

        $bets = abs($user->transactions()
            ->where('type', 'bet')
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount'));

        $wins = $user->transactions()
            ->where('type', 'win')
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'from' => $from->format('Y-m-d'),
                    'to' => $to->format('Y-m-d'),
                ],
                'deposits' => $deposits,
                'withdrawals' => $withdrawals,
                'bets' => $bets,
                'wins' => $wins,
                'net' => $deposits - $withdrawals + $wins - $bets,
                'current_balance' => (float) $user->balance,
            ],
        ]);
    }

    /**
     * GET /api/affiliate/dashboard
     */
    public function affiliateDashboard(Request $request): JsonResponse
    {
        $data = $this->affiliateService->getDashboard($request->user());

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * GET /api/affiliate/members
     */
    public function affiliateMembers(Request $request): JsonResponse
    {
        $data = $this->affiliateService->getReferredMembers($request->user());

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * GET /api/affiliate/commissions
     */
    public function affiliateCommissions(Request $request): JsonResponse
    {
        $data = $this->affiliateService->getCommissions($request->user());

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * POST /api/affiliate/withdraw
     */
    public function affiliateWithdraw(Request $request): JsonResponse
    {
        $result = $this->affiliateService->withdrawCommissions($request->user());

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * GET /api/affiliate/link
     */
    public function affiliateLink(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->referral_code) {
            $user->generateReferralCode();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'referral_code' => $user->referral_code,
                'referral_link' => url("/register?ref={$user->referral_code}"),
            ],
        ]);
    }
}
