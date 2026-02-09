<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Services\BalanceService;
use App\Services\WithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceManageController extends Controller
{
    public function __construct(
        private BalanceService $balanceService,
        private WithdrawalService $withdrawalService,
    ) {}

    /**
     * GET /admin/finance/deposits
     */
    public function deposits(Request $request): JsonResponse
    {
        $query = Deposit::with('user:id,name,phone');

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        if ($from = $request->date('from')) {
            $query->where('created_at', '>=', $from);
        }

        if ($to = $request->date('to')) {
            $query->where('created_at', '<=', $to->endOfDay());
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('created_at', 'desc')->paginate(20),
        ]);
    }

    /**
     * PUT /admin/finance/deposits/{deposit}/approve
     */
    public function approveDeposit(Request $request, Deposit $deposit): JsonResponse
    {
        if ($deposit->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'รายการนี้ดำเนินการแล้ว',
            ], 422);
        }

        $deposit->update([
            'status' => 'approved',
            'manual_matched_by' => $request->user()->id,
        ]);

        // Credit balance
        $this->balanceService->credit(
            $deposit->user,
            (float) $deposit->amount,
            "ฝากเงิน (อนุมัติ) #{$deposit->id}",
            TransactionType::Deposit,
            $deposit->id,
            'deposit',
        );

        AdminLog::log($request->user()->id, 'approve_deposit', "อนุมัติฝากเงิน #{$deposit->id}: {$deposit->amount} บาท", 'deposit', $deposit->id);

        return response()->json([
            'success' => true,
            'message' => 'อนุมัติสำเร็จ',
        ]);
    }

    /**
     * PUT /admin/finance/deposits/{deposit}/reject
     */
    public function rejectDeposit(Request $request, Deposit $deposit): JsonResponse
    {
        if ($deposit->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'รายการนี้ดำเนินการแล้ว',
            ], 422);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $deposit->update([
            'status' => 'rejected',
            'manual_matched_by' => $request->user()->id,
        ]);

        AdminLog::log($request->user()->id, 'reject_deposit', "ปฏิเสธฝากเงิน #{$deposit->id}: " . ($request->reason ?? 'N/A'), 'deposit', $deposit->id);

        return response()->json([
            'success' => true,
            'message' => 'ปฏิเสธสำเร็จ',
        ]);
    }

    /**
     * GET /admin/finance/withdrawals
     */
    public function withdrawals(Request $request): JsonResponse
    {
        $query = Withdrawal::with(['user:id,name,phone', 'bankAccount']);

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('created_at', 'desc')->paginate(20),
        ]);
    }

    /**
     * PUT /admin/finance/withdrawals/{withdrawal}/approve
     */
    public function approveWithdrawal(Request $request, Withdrawal $withdrawal): JsonResponse
    {
        $result = $this->withdrawalService->approve($withdrawal, $request->user()->id);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * PUT /admin/finance/withdrawals/{withdrawal}/reject
     */
    public function rejectWithdrawal(Request $request, Withdrawal $withdrawal): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $result = $this->withdrawalService->reject($withdrawal, $request->reason, $request->user()->id);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * GET /admin/finance/report
     */
    public function report(Request $request): JsonResponse
    {
        $from = $request->date('from', now()->startOfMonth());
        $to = $request->date('to', now());

        $deposits = Transaction::where('type', 'deposit')
            ->whereBetween('created_at', [$from, $to->endOfDay()])
            ->sum('amount');

        $withdrawals = abs(Transaction::where('type', 'withdraw')
            ->whereBetween('created_at', [$from, $to->endOfDay()])
            ->sum('amount'));

        $bets = abs(Transaction::where('type', 'bet')
            ->whereBetween('created_at', [$from, $to->endOfDay()])
            ->sum('amount'));

        $wins = Transaction::where('type', 'win')
            ->whereBetween('created_at', [$from, $to->endOfDay()])
            ->sum('amount');

        $commissions = Transaction::where('type', 'commission')
            ->whereBetween('created_at', [$from, $to->endOfDay()])
            ->sum('amount');

        $profit = $bets - $wins - $commissions;

        // Daily breakdown
        $dailyStats = Transaction::whereBetween('created_at', [$from, $to->endOfDay()])
            ->selectRaw("DATE(created_at) as date, type, SUM(ABS(amount)) as total")
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        return response()->json([
            'success' => true,
            'data' => [
                'period' => ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')],
                'summary' => [
                    'deposits' => $deposits,
                    'withdrawals' => $withdrawals,
                    'bets' => $bets,
                    'wins' => $wins,
                    'commissions' => $commissions,
                    'profit' => $profit,
                    'margin' => $bets > 0 ? round($profit / $bets * 100, 2) : 0,
                ],
                'daily' => $dailyStats,
            ],
        ]);
    }
}
