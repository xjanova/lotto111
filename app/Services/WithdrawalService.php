<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Events\WithdrawalCompleted;
use App\Models\AdminLog;
use App\Models\User;
use App\Models\UserBankAccount;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

class WithdrawalService
{
    public function __construct(
        private BalanceService $balanceService,
    ) {}

    /**
     * Create a withdrawal request
     */
    public function create(User $user, float $amount, int $bankAccountId): array
    {
        $minAmount = config('payment.withdrawal.min_amount', 300);
        $maxAmount = config('payment.withdrawal.max_amount', 50000);

        if ($amount < $minAmount) {
            return ['success' => false, 'message' => "ถอนขั้นต่ำ {$minAmount} บาท"];
        }

        if ($amount > $maxAmount) {
            return ['success' => false, 'message' => "ถอนสูงสุด {$maxAmount} บาท"];
        }

        if (! $user->hasBalance($amount)) {
            return ['success' => false, 'message' => 'ยอดเงินไม่เพียงพอ'];
        }

        // Check daily limit
        $dailyLimit = config('payment.withdrawal.daily_limit', 200000);
        $todayTotal = Withdrawal::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->whereIn('status', ['pending', 'approved', 'processing', 'completed'])
            ->sum('amount');

        if ($todayTotal + $amount > $dailyLimit) {
            return ['success' => false, 'message' => "เกินวงเงินถอนรายวัน (คงเหลือ " . ($dailyLimit - $todayTotal) . " บาท)"];
        }

        // Verify bank account belongs to user
        $bankAccount = UserBankAccount::where('id', $bankAccountId)
            ->where('user_id', $user->id)
            ->first();

        if (! $bankAccount) {
            return ['success' => false, 'message' => 'ไม่พบบัญชีธนาคาร'];
        }

        // Check pending withdrawals
        $pendingCount = Withdrawal::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        if ($pendingCount > 0) {
            return ['success' => false, 'message' => 'มีรายการถอนที่รอดำเนินการอยู่'];
        }

        return DB::transaction(function () use ($user, $amount, $bankAccount) {
            // Deduct balance immediately (hold)
            $this->balanceService->debit(
                $user,
                $amount,
                'ถอนเงิน (รอดำเนินการ)',
                TransactionType::Withdraw,
            );

            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'bank_account_id' => $bankAccount->id,
                'amount' => $amount,
                'status' => 'pending',
            ]);

            // Auto-approve if within threshold
            $autoApproveMax = config('payment.withdrawal.auto_approve_max', 0);
            if ($autoApproveMax > 0 && $amount <= $autoApproveMax) {
                $this->approve($withdrawal);
            }

            return [
                'success' => true,
                'message' => 'แจ้งถอนเงินสำเร็จ รอดำเนินการ',
                'withdrawal' => $withdrawal,
            ];
        });
    }

    /**
     * Approve a withdrawal
     */
    public function approve(Withdrawal $withdrawal, ?int $adminId = null): array
    {
        if (! $withdrawal->isPending()) {
            return ['success' => false, 'message' => 'รายการนี้ดำเนินการแล้ว'];
        }

        $withdrawal->update([
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        if ($adminId) {
            AdminLog::log($adminId, 'approve_withdrawal', "อนุมัติถอนเงิน #{$withdrawal->id} จำนวน {$withdrawal->amount} บาท", 'withdrawal', $withdrawal->id);
        }

        return ['success' => true, 'message' => 'อนุมัติสำเร็จ'];
    }

    /**
     * Complete a withdrawal (mark as transferred)
     */
    public function complete(Withdrawal $withdrawal): array
    {
        if ($withdrawal->status !== 'approved') {
            return ['success' => false, 'message' => 'รายการนี้ยังไม่ได้รับอนุมัติ'];
        }

        $withdrawal->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        WithdrawalCompleted::dispatch($withdrawal);

        return ['success' => true, 'message' => 'โอนเงินสำเร็จ'];
    }

    /**
     * Reject a withdrawal
     */
    public function reject(Withdrawal $withdrawal, ?string $note = null, ?int $adminId = null): array
    {
        if (! $withdrawal->isPending()) {
            return ['success' => false, 'message' => 'รายการนี้ดำเนินการแล้ว'];
        }

        return DB::transaction(function () use ($withdrawal, $note, $adminId) {
            $withdrawal->update([
                'status' => 'rejected',
                'note' => $note,
                'approved_by' => $adminId,
                'approved_at' => now(),
            ]);

            // Refund balance
            $this->balanceService->credit(
                $withdrawal->user,
                (float) $withdrawal->amount,
                "คืนเงินถอน (ปฏิเสธ) #{$withdrawal->id}",
                TransactionType::Refund,
                $withdrawal->id,
                'withdrawal',
            );

            if ($adminId) {
                AdminLog::log($adminId, 'reject_withdrawal', "ปฏิเสธถอนเงิน #{$withdrawal->id}: {$note}", 'withdrawal', $withdrawal->id);
            }

            return ['success' => true, 'message' => 'ปฏิเสธรายการสำเร็จ'];
        });
    }

    /**
     * Get withdrawal history for user
     */
    public function getHistory(User $user, int $limit = 20): mixed
    {
        return Withdrawal::where('user_id', $user->id)
            ->with('bankAccount')
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }
}
