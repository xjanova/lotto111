<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct(
        private BalanceService $balanceService,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $query = User::where('role', 'member')->with('riskProfile');

        if ($search = $request->string('search')->value()) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")->orWhere('referral_code', $search));
        }
        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        $paginated = $query->orderBy($request->string('sort', 'created_at')->value(), $request->string('dir', 'desc')->value())
            ->paginate($request->integer('per_page', 20));

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $paginated]);
        }

        $members = collect($paginated->items())->map(fn ($u) => [
            'id' => $u->id, 'name' => $u->name, 'phone' => $u->phone,
            'balance' => (float) $u->balance, 'vip_level' => $u->vip_level ?? 0,
            'status' => $u->status?->value ?? $u->status ?? 'active',
            'created_at' => $u->created_at?->format('d/m/Y H:i'),
        ])->toArray();

        return view('admin.members.index', [
            'members' => $members, 'total' => $paginated->total(),
            'page' => $paginated->currentPage(), 'lastPage' => $paginated->lastPage(),
        ]);
    }

    public function show(User $user): View|JsonResponse
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

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'data' => ['user' => $user, 'stats' => $stats]]);
        }

        return view('admin.members.show', ['user' => $user, 'stats' => $stats]);
    }

    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $request->validate(['status' => 'required|string|in:active,suspended,banned', 'reason' => 'nullable|string|max:500']);
        $oldStatus = $user->status?->value ?? $user->status ?? 'unknown';
        $user->update(['status' => $request->status]);
        AdminLog::log($request->user()->id, 'update_member_status', "เปลี่ยนสถานะ {$user->name} จาก {$oldStatus} เป็น {$request->status}" . ($request->reason ? ": {$request->reason}" : ''), 'user', $user->id);
        return response()->json(['success' => true, 'message' => 'อัปเดตสถานะสำเร็จ']);
    }

    public function adjustCredit(Request $request, User $user): JsonResponse
    {
        $request->validate(['amount' => 'required|numeric|not_in:0', 'reason' => 'required|string|max:500']);
        $amount = (float) $request->amount;
        if ($amount > 0) {
            $this->balanceService->credit($user, $amount, "Admin เติมเครดิต: {$request->reason}", TransactionType::Adjustment);
        } else {
            $this->balanceService->debit($user, abs($amount), "Admin หักเครดิต: {$request->reason}", TransactionType::Adjustment);
        }
        AdminLog::log($request->user()->id, 'adjust_credit', "ปรับเครดิต {$user->name}: {$amount} บาท - {$request->reason}", 'user', $user->id);
        return response()->json(['success' => true, 'message' => 'ปรับเครดิตสำเร็จ', 'new_balance' => (float) $user->fresh()->balance]);
    }
}
