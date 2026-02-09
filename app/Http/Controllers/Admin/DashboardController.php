<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\LotteryRound;
use App\Models\LotteryType;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /admin/
     */
    public function index(Request $request): View|JsonResponse
    {
        $today = today();

        $totalMembers = User::where('role', 'member')->count();
        $newMembersToday = User::where('role', 'member')->whereDate('created_at', $today)->count();

        $todayDeposits = Deposit::whereDate('created_at', $today)->where('status', 'credited')->sum('amount');
        $depositCountToday = Deposit::whereDate('created_at', $today)->where('status', 'credited')->count();
        $todayWithdrawals = Withdrawal::whereDate('created_at', $today)->whereIn('status', ['approved', 'completed'])->sum('amount');
        $withdrawalCountToday = Withdrawal::whereDate('created_at', $today)->whereIn('status', ['approved', 'completed'])->count();

        $todayBets = abs(Transaction::where('type', 'bet')->whereDate('created_at', $today)->sum('amount'));
        $betCountToday = Ticket::whereDate('created_at', $today)->count();
        $todayWins = Transaction::where('type', 'win')->whereDate('created_at', $today)->sum('amount');
        $todayProfit = $todayBets - $todayWins;

        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();

        $stats = [
            'total_members' => $totalMembers,
            'new_members_today' => $newMembersToday,
            'deposits_today' => $todayDeposits,
            'deposit_count_today' => $depositCountToday,
            'withdrawals_today' => $todayWithdrawals,
            'withdrawal_count_today' => $withdrawalCountToday,
            'bets_today' => $todayBets,
            'bet_count_today' => $betCountToday,
            'profit_today' => $todayProfit,
            'pending_withdrawals' => $pendingWithdrawals,
        ];

        // Chart data
        $chartData = $this->buildChartData();

        // Recent activity
        $recentDeposits = Deposit::with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'user' => $d->user?->name,
                'amount' => (float) $d->amount,
                'status' => $d->status,
                'status_text' => match ($d->status) { 'credited' => 'อนุมัติ', 'approved' => 'อนุมัติ', 'pending' => 'รอตรวจสอบ', default => $d->status },
                'time' => $d->created_at?->diffForHumans(),
            ])->toArray();

        $recentBets = Ticket::with(['user:id,name', 'round.lotteryType'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'user' => $t->user?->name,
                'lottery_type' => $t->round?->lotteryType?->name ?? '-',
                'number' => $t->number ?? '-',
                'amount' => (float) ($t->amount ?? $t->total_amount ?? 0),
                'time' => $t->created_at?->diffForHumans(),
            ])->toArray();

        $activeRounds = LotteryRound::with('lotteryType')
            ->whereIn('status', ['open', 'upcoming'])
            ->orderBy('close_at')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'type' => $r->lotteryType?->name ?? '-',
                'code' => $r->round_code,
                'status' => $r->status?->value ?? $r->status ?? 'unknown',
                'close_at' => $r->close_at?->format('d/m H:i'),
                'total_bets' => 0,
            ])->toArray();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => compact('stats', 'chartData', 'recentDeposits', 'recentBets', 'activeRounds')]);
        }

        return view('admin.dashboard', compact('stats', 'chartData', 'recentDeposits', 'recentBets', 'activeRounds'));
    }

    private function buildChartData(): array
    {
        $sevenDaysAgo = now()->subDays(6)->startOfDay();
        $thirtyDaysAgo = now()->subDays(29)->startOfDay();

        // Batch query: 7-day deposits grouped by date
        $depositsByDay = Deposit::where('status', 'credited')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->selectRaw("DATE(created_at) as date, SUM(amount) as total")
            ->groupBy('date')->pluck('total', 'date');

        $withdrawalsByDay = Withdrawal::whereIn('status', ['approved', 'completed'])
            ->where('created_at', '>=', $sevenDaysAgo)
            ->selectRaw("DATE(created_at) as date, SUM(amount) as total")
            ->groupBy('date')->pluck('total', 'date');

        $betsByDay = Transaction::where('type', 'bet')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->selectRaw("DATE(created_at) as date, SUM(ABS(amount)) as total")
            ->groupBy('date')->pluck('total', 'date');

        $winsByDay = Transaction::where('type', 'win')
            ->where('created_at', '>=', $sevenDaysAgo)
            ->selectRaw("DATE(created_at) as date, SUM(amount) as total")
            ->groupBy('date')->pluck('total', 'date');

        $labels = [];
        $deposits = [];
        $withdrawals = [];
        $profit = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');
            $deposits[] = (float) ($depositsByDay[$dateKey] ?? 0);
            $withdrawals[] = (float) ($withdrawalsByDay[$dateKey] ?? 0);
            $profit[] = (float) ($betsByDay[$dateKey] ?? 0) - (float) ($winsByDay[$dateKey] ?? 0);
        }

        // Batch query: 30-day member growth
        $membersByDay = User::where('role', 'member')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw("DATE(created_at) as date, COUNT(*) as total")
            ->groupBy('date')->pluck('total', 'date');

        $memberLabels = [];
        $memberData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $memberLabels[] = $date->format('d/m');
            $memberData[] = (int) ($membersByDay[$dateKey] ?? 0);
        }

        // Lottery type breakdown - batch query with join
        $activeLotteryTypes = LotteryType::where('is_active', true)->get();
        $lotteryTypeIds = $activeLotteryTypes->pluck('id')->toArray();
        $lotteryTypes = $activeLotteryTypes->pluck('name')->toArray();

        $lotteryAmountsMap = collect();
        if (!empty($lotteryTypeIds)) {
            $lotteryAmountsMap = Ticket::join('lottery_rounds', 'tickets.lottery_round_id', '=', 'lottery_rounds.id')
                ->whereIn('lottery_rounds.lottery_type_id', $lotteryTypeIds)
                ->where('tickets.created_at', '>=', now()->subDays(30))
                ->selectRaw('lottery_rounds.lottery_type_id, SUM(tickets.total_amount) as total')
                ->groupBy('lottery_rounds.lottery_type_id')
                ->pluck('total', 'lottery_type_id');
        }

        $lotteryAmounts = $activeLotteryTypes->map(fn ($lt) => (float) ($lotteryAmountsMap[$lt->id] ?? 0))->toArray();
        $colors = ['#3b82f6', '#22c55e', '#f97316', '#8b5cf6', '#ec4899', '#14b8a6', '#f59e0b', '#ef4444', '#6366f1', '#84cc16'];

        return [
            'revenue' => ['labels' => $labels, 'deposits' => $deposits, 'withdrawals' => $withdrawals, 'profit' => $profit],
            'members' => ['labels' => $memberLabels, 'data' => $memberData],
            'deposit_withdraw' => ['labels' => $labels, 'deposits' => $deposits, 'withdrawals' => $withdrawals],
            'lottery_types' => ['labels' => array_slice($lotteryTypes, 0, 8), 'data' => array_slice($lotteryAmounts, 0, 8), 'colors' => array_slice($colors, 0, 8)],
        ];
    }
}
