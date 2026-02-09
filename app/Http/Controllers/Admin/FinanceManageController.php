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
use Illuminate\Contracts\View\View;
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
    public function deposits(Request $request): View|JsonResponse
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

        $paginated = $query->orderBy('created_at', 'desc')->paginate(20);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $paginated]);
        }

        $transactions = collect($paginated->items())->map(fn ($d) => [
            'id' => $d->id, 'user' => $d->user?->name ?? '-', 'amount' => (float) $d->amount,
            'fee' => 0, 'channel' => $d->method ?? 'SMS', 'status' => $d->status,
            'created_at' => $d->created_at?->format('d/m/Y H:i'),
        ])->toArray();

        $todayDeposits = Deposit::whereDate('created_at', today())->where('status', 'credited')->sum('amount');
        $todayWithdrawals = Withdrawal::whereDate('created_at', today())->whereIn('status', ['approved', 'completed'])->sum('amount');
        $pendingCount = Deposit::where('status', 'pending')->count() + Withdrawal::where('status', 'pending')->count();

        return view('admin.finance.deposits', compact('transactions', 'todayDeposits', 'todayWithdrawals', 'pendingCount'));
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
    public function withdrawals(Request $request): View|JsonResponse
    {
        $query = Withdrawal::with(['user:id,name,phone', 'bankAccount']);

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        $paginated = $query->orderBy('created_at', 'desc')->paginate(20);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $paginated,
            ]);
        }

        $withdrawals = collect($paginated->items())->map(fn ($w) => [
            'id' => $w->id,
            'user' => $w->user?->name ?? '-',
            'amount' => (float) $w->amount,
            'bank_name' => $w->bankAccount?->bank_name ?? '-',
            'account_number' => $w->bankAccount?->account_number ?? '-',
            'status' => $w->status,
            'created_at' => $w->created_at?->format('d/m/Y H:i'),
        ])->toArray();

        return view('admin.finance.withdrawals', compact('withdrawals'));
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
    public function report(Request $request): View|JsonResponse
    {
        $from = $request->date('from', now()->startOfMonth());
        $to = $request->date('to', now());

        $deposits = Transaction::where('type', 'deposit')
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->sum('amount');

        $withdrawals = abs(Transaction::where('type', 'withdraw')
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->sum('amount'));

        $bets = abs(Transaction::where('type', 'bet')
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->sum('amount'));

        $wins = Transaction::where('type', 'win')
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->sum('amount');

        $commissions = Transaction::where('type', 'commission')
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->sum('amount');

        $profit = $bets - $wins - $commissions;

        // Daily breakdown
        $dailyStats = Transaction::whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->selectRaw("DATE(created_at) as date, type, SUM(ABS(amount)) as total")
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        $summary = [
            'deposits' => $deposits,
            'withdrawals' => $withdrawals,
            'bets' => $bets,
            'wins' => $wins,
            'commissions' => $commissions,
            'profit' => $profit,
            'margin' => $bets > 0 ? round($profit / $bets * 100, 2) : 0,
        ];

        $period = ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'period' => $period,
                    'summary' => $summary,
                    'daily' => $dailyStats,
                ],
            ]);
        }

        // Build chart data for daily breakdown
        $dailyChart = ['labels' => [], 'bets' => [], 'wins' => [], 'profit' => []];
        foreach ($dailyStats as $date => $items) {
            $dailyChart['labels'][] = $date;
            $dayBets = $items->where('type', 'bet')->first()?->total ?? 0;
            $dayWins = $items->where('type', 'win')->first()?->total ?? 0;
            $dailyChart['bets'][] = (float) $dayBets;
            $dailyChart['wins'][] = (float) $dayWins;
            $dailyChart['profit'][] = (float) $dayBets - (float) $dayWins;
        }

        return view('admin.finance.report', compact('summary', 'period', 'dailyChart'));
    }
}
