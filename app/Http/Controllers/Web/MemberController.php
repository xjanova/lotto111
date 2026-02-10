<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCommission;
use App\Models\LotteryRound;
use App\Models\LotteryType;
use App\Models\User;
use App\Services\AffiliateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function dashboard(): View
    {
        $user = Auth::user();

        // Stats
        $totalDeposits = $user->deposits()->where('status', 'credited')->sum('amount');
        $totalWithdrawals = $user->withdrawals()->whereIn('status', ['approved', 'completed'])->sum('amount');
        $totalBets = abs($user->transactions()->where('type', 'bet')->sum('amount'));
        $totalWins = $user->transactions()->where('type', 'win')->sum('amount');
        $totalTickets = $user->tickets()->count();
        $pendingWithdrawals = $user->withdrawals()->where('status', 'pending')->sum('amount');

        // Recent transactions
        $recentTransactions = $user->transactions()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Today's tickets
        $todayTickets = $user->tickets()
            ->with('lotteryRound.lotteryType')
            ->whereDate('bet_at', today())
            ->orderByDesc('bet_at')
            ->limit(10)
            ->get();

        // Open rounds
        $openRounds = LotteryRound::with('lotteryType')
            ->where('status', 'open')
            ->orderBy('close_at')
            ->limit(6)
            ->get();

        // Affiliate
        $referralCount = User::where('referred_by', $user->id)->count();
        $pendingCommission = AffiliateCommission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('commission');

        return view('member.dashboard', compact(
            'user',
            'totalDeposits',
            'totalWithdrawals',
            'totalBets',
            'totalWins',
            'totalTickets',
            'pendingWithdrawals',
            'recentTransactions',
            'todayTickets',
            'openRounds',
            'referralCount',
            'pendingCommission',
        ));
    }

    public function referral(): View
    {
        $user = Auth::user();
        $affiliate = app(AffiliateService::class)->getDashboard($user);
        $referrals = User::where('referred_by', $user->id)
            ->select('id', 'name', 'phone', 'created_at', 'last_login_at')
            ->orderByDesc('created_at')
            ->paginate(20);

        $commissions = AffiliateCommission::where('user_id', $user->id)
            ->with('fromUser:id,name')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('member.referral', compact('user', 'affiliate', 'referrals', 'commissions'));
    }

    public function withdrawCommission(Request $request)
    {
        $user = Auth::user();
        $result = app(AffiliateService::class)->withdrawCommissions($user);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function profile(): View
    {
        $user = Auth::user();
        $user->load('primaryBankAccount', 'bankAccounts');

        return view('member.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'line_id' => 'nullable|string|max:100',
        ]);

        Auth::user()->update($validated);

        return response()->json(['success' => true, 'message' => 'อัปเดตข้อมูลสำเร็จ']);
    }
}
