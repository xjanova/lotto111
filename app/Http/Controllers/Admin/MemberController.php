<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct(
        private BalanceService $balanceService,
    ) {}

    /**
     * GET /admin/members
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::where('role', 'member')
            ->with('riskProfile');

        if ($search = $request->string('search')->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('referral_code', $search);
            });
        }

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        $sortBy = $request->string('sort', 'created_at')->value();
        $sortDir = $request->string('dir', 'desc')->value();
        $query->orderBy($sortBy, $sortDir);

        return response()->json([
            'success' => true,
            'data' => $query->paginate($request->integer('per_page', 20)),
        ]);
    }

    /**
     * GET /admin/members/{user}
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['riskProfile', 'primaryBankAccount', 'bankAccounts']);

        $stats = [
            'total_deposits' => $user->deposits()->where('status', 'credited')->sum('amount'),
            'total_withdrawals' => $user->withdrawals()->whereIn('status', ['approved', 'completed'])->sum('amount'),
            'total_bets' => abs($user->transactions()->where('type', 'bet')->sum('amount')),
            'total_wins' => $user->transactions()->where('type', 'win')->sum('amount'),
            'total_tickets' => $user->tickets()->count(),
            'referrals_count' => User::where('referred_by', $user->id)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'stats' => $stats,
            ],
        ]);
    }

    /**
     * PUT /admin/members/{user}/status
     */
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:active,suspended,banned',
            'reason' => 'nullable|string|max:500',
        ]);

        $oldStatus = $user->status?->value;
        $user->update(['status' => $request->status]);

        AdminLog::log(
            $request->user()->id,
            'update_member_status',
            "เปลี่ยนสถานะ {$user->name} จาก {$oldStatus} เป็น {$request->status}" . ($request->reason ? ": {$request->reason}" : ''),
            'user',
            $user->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'อัปเดตสถานะสำเร็จ',
        ]);
    }

    /**
     * POST /admin/members/{user}/credit
     */
    public function adjustCredit(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|not_in:0',
            'reason' => 'required|string|max:500',
        ]);

        $amount = (float) $request->amount;

        if ($amount > 0) {
            $this->balanceService->credit(
                $user,
                $amount,
                "Admin เติมเครดิต: {$request->reason}",
                TransactionType::Adjustment,
            );
        } else {
            $this->balanceService->debit(
                $user,
                abs($amount),
                "Admin หักเครดิต: {$request->reason}",
                TransactionType::Adjustment,
            );
        }

        AdminLog::log(
            $request->user()->id,
            'adjust_credit',
            "ปรับเครดิต {$user->name}: {$amount} บาท - {$request->reason}",
            'user',
            $user->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'ปรับเครดิตสำเร็จ',
            'new_balance' => (float) $user->fresh()->balance,
        ]);
    }
}
