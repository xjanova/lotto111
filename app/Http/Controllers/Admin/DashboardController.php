<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * GET /admin/
     */
    public function index(): JsonResponse
    {
        $today = today();

        $totalMembers = User::where('role', 'member')->count();
        $newMembersToday = User::where('role', 'member')->whereDate('created_at', $today)->count();
        $activeToday = User::where('role', 'member')->whereDate('last_login_at', $today)->count();

        $todayDeposits = Deposit::whereDate('created_at', $today)->where('status', 'credited')->sum('amount');
        $todayWithdrawals = Withdrawal::whereDate('created_at', $today)->whereIn('status', ['approved', 'completed'])->sum('amount');

        $todayBets = Transaction::where('type', 'bet')->whereDate('created_at', $today)->sum('amount');
        $todayWins = Transaction::where('type', 'win')->whereDate('created_at', $today)->sum('amount');
        $todayProfit = abs($todayBets) - $todayWins;

        $pendingDeposits = Deposit::where('status', 'pending')->count();
        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();

        $todayTickets = Ticket::whereDate('bet_at', $today)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'members' => [
                    'total' => $totalMembers,
                    'new_today' => $newMembersToday,
                    'active_today' => $activeToday,
                ],
                'finance' => [
                    'deposits_today' => $todayDeposits,
                    'withdrawals_today' => $todayWithdrawals,
                    'pending_deposits' => $pendingDeposits,
                    'pending_withdrawals' => $pendingWithdrawals,
                ],
                'betting' => [
                    'tickets_today' => $todayTickets,
                    'bets_today' => abs($todayBets),
                    'wins_today' => $todayWins,
                    'profit_today' => $todayProfit,
                ],
            ],
        ]);
    }
}
