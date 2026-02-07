<?php

namespace App\Services\Deposit;

use App\Enums\SmsDepositStatus;
use App\Enums\TransactionType;
use App\Models\Deposit;
use App\Models\User;
use App\Models\SmsCheckerDevice;
use App\Models\SmsPaymentNotification;
use App\Models\UniquePaymentAmount;
use App\Services\BalanceService;
use App\Events\DepositMatched;
use App\Events\DepositCredited;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SmsDepositService
{
    public function __construct(
        private BalanceService $balanceService,
        private SmsPaymentProcessorService $smsProcessor,
    ) {}

    /**
     * สร้างรายการฝากเงินอัตโนมัติ (SMS)
     * - สร้าง unique decimal amount
     * - สร้าง Deposit record
     * - Return ข้อมูลสำหรับแสดงให้ลูกค้า
     */
    public function createDeposit(User $user, float $amount): array
    {
        // Validate limits
        $this->validateDepositLimits($user, $amount);

        return DB::transaction(function () use ($user, $amount) {
            // สร้าง Deposit record
            $deposit = Deposit::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'method' => 'sms_auto',
                'status' => SmsDepositStatus::WaitingTransfer->value,
                'expires_at' => now()->addMinutes(config('smschecker.unique_amount_expiry', 30)),
            ]);

            // สร้าง unique amount ผ่าน smschecker
            $uniqueAmount = UniquePaymentAmount::generate(
                baseAmount: $amount,
                transactionId: $deposit->id,
                transactionType: 'deposit',
                expiryMinutes: config('smschecker.unique_amount_expiry', 30),
            );

            if (!$uniqueAmount) {
                throw new \RuntimeException('ไม่สามารถสร้างยอดเงินได้ มีรายการค้างจำนวนมากที่ยอดนี้ กรุณาลองใหม่');
            }

            // อัพเดท deposit ด้วย unique amount
            $deposit->update([
                'unique_amount' => $uniqueAmount->unique_amount,
                'unique_amount_id' => $uniqueAmount->id,
            ]);

            // ข้อมูลบัญชีรับเงิน
            $receivingAccount = config('smschecker.receiving_account');

            Log::info('SMS Deposit created', [
                'user_id' => $user->id,
                'deposit_id' => $deposit->id,
                'base_amount' => $amount,
                'unique_amount' => $uniqueAmount->unique_amount,
                'expires_at' => $uniqueAmount->expires_at,
            ]);

            return [
                'deposit_id' => $deposit->id,
                'base_amount' => number_format($amount, 2, '.', ''),
                'transfer_amount' => number_format((float) $uniqueAmount->unique_amount, 2, '.', ''),
                'display_amount' => '฿' . number_format((float) $uniqueAmount->unique_amount, 2),
                'expires_at' => $uniqueAmount->expires_at->toIso8601String(),
                'expires_in_seconds' => now()->diffInSeconds($uniqueAmount->expires_at),
                'bank' => [
                    'name' => $receivingAccount['bank_name'],
                    'code' => $receivingAccount['bank_code'],
                    'account_number' => $receivingAccount['account_number'],
                    'account_name' => $receivingAccount['account_name'],
                ],
                'promptpay' => [
                    'number' => $receivingAccount['promptpay_number'],
                    'qr_data' => $this->generatePromptPayQR(
                        $receivingAccount['promptpay_number'],
                        (float) $uniqueAmount->unique_amount,
                    ),
                ],
                'instructions' => [
                    'th' => "กรุณาโอนเงินจำนวน ฿" . number_format((float) $uniqueAmount->unique_amount, 2) . " ภายใน " . config('smschecker.unique_amount_expiry', 30) . " นาที",
                    'en' => "Please transfer ฿" . number_format((float) $uniqueAmount->unique_amount, 2) . " within " . config('smschecker.unique_amount_expiry', 30) . " minutes",
                ],
            ];
        });
    }

    /**
     * จัดการเมื่อ SMS จับคู่ได้ → เติมเงินเข้ากระเป๋า
     * ถูกเรียกจาก SmsPaymentProcessorService หลังจับคู่สำเร็จ
     */
    public function handleSmsMatch(SmsPaymentNotification $notification, UniquePaymentAmount $uniqueAmount): bool
    {
        return DB::transaction(function () use ($notification, $uniqueAmount) {
            // หา Deposit record
            $deposit = Deposit::find($uniqueAmount->transaction_id);

            if (!$deposit) {
                Log::error('SMS Deposit: Deposit not found for matched amount', [
                    'notification_id' => $notification->id,
                    'transaction_id' => $uniqueAmount->transaction_id,
                ]);
                return false;
            }

            if ($deposit->status === SmsDepositStatus::Credited->value) {
                Log::warning('SMS Deposit: Already credited', [
                    'deposit_id' => $deposit->id,
                ]);
                return false;
            }

            $user = User::find($deposit->user_id);
            if (!$user) {
                Log::error('SMS Deposit: User not found', ['user_id' => $deposit->user_id]);
                return false;
            }

            // อัพเดท Deposit status
            $deposit->update([
                'status' => SmsDepositStatus::Matched->value,
                'matched_at' => now(),
                'sms_notification_id' => $notification->id,
                'matched_bank' => $notification->bank,
                'matched_reference' => $notification->reference_number,
            ]);

            // เติมเงินเข้ากระเป๋า
            $creditResult = $this->balanceService->credit(
                user: $user,
                amount: $deposit->amount, // ใช้ base amount ไม่ใช่ unique amount
                description: "ฝากเงินอัตโนมัติ (SMS) #{$deposit->id}",
                type: TransactionType::Deposit,
                referenceId: $deposit->id,
                referenceType: 'deposit',
            );

            // อัพเดทเป็น Credited
            $deposit->update([
                'status' => SmsDepositStatus::Credited->value,
                'credited_at' => now(),
                'transaction_id' => $creditResult['transaction_id'] ?? null,
            ]);

            // อัพเดท user risk profile (สำหรับ risk engine)
            $this->updateUserDepositStats($user, $deposit->amount);

            // Broadcast event (real-time notification)
            event(new DepositCredited($deposit, $user));

            Log::info('SMS Deposit credited', [
                'user_id' => $user->id,
                'deposit_id' => $deposit->id,
                'amount' => $deposit->amount,
                'bank' => $notification->bank,
            ]);

            return true;
        });
    }

    /**
     * เช็คสถานะ deposit real-time
     */
    public function getDepositStatus(int $depositId, User $user): ?array
    {
        $deposit = Deposit::where('id', $depositId)
            ->where('user_id', $user->id)
            ->first();

        if (!$deposit) {
            return null;
        }

        $status = SmsDepositStatus::tryFrom($deposit->status);

        return [
            'deposit_id' => $deposit->id,
            'amount' => number_format((float) $deposit->amount, 2, '.', ''),
            'unique_amount' => $deposit->unique_amount ? number_format((float) $deposit->unique_amount, 2, '.', '') : null,
            'status' => $deposit->status,
            'status_label' => $status?->label() ?? $deposit->status,
            'status_color' => $status?->color() ?? 'gray',
            'is_terminal' => $status?->isTerminal() ?? false,
            'created_at' => $deposit->created_at->toIso8601String(),
            'expires_at' => $deposit->expires_at?->toIso8601String(),
            'remaining_seconds' => $deposit->expires_at ? max(0, now()->diffInSeconds($deposit->expires_at, false)) : 0,
            'matched_at' => $deposit->matched_at?->toIso8601String(),
            'credited_at' => $deposit->credited_at?->toIso8601String(),
            'matched_bank' => $deposit->matched_bank,
        ];
    }

    /**
     * ยกเลิกรายการฝาก
     */
    public function cancelDeposit(int $depositId, User $user): bool
    {
        return DB::transaction(function () use ($depositId, $user) {
            $deposit = Deposit::where('id', $depositId)
                ->where('user_id', $user->id)
                ->whereIn('status', [
                    SmsDepositStatus::WaitingTransfer->value,
                ])
                ->first();

            if (!$deposit) {
                return false;
            }

            // ยกเลิก unique amount
            if ($deposit->unique_amount_id) {
                UniquePaymentAmount::where('id', $deposit->unique_amount_id)
                    ->where('status', 'reserved')
                    ->update(['status' => 'cancelled']);
            }

            $deposit->update([
                'status' => SmsDepositStatus::Cancelled->value,
                'cancelled_at' => now(),
            ]);

            Log::info('SMS Deposit cancelled', [
                'user_id' => $user->id,
                'deposit_id' => $deposit->id,
            ]);

            return true;
        });
    }

    /**
     * ประวัติฝากเงินของ user
     */
    public function getDepositHistory(User $user, int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Deposit::where('user_id', $user->id)
            ->where('method', 'sms_auto')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Reconcile - ตรวจสอบรายการค้าง
     * เรียกจาก Scheduler ทุก 5 นาที
     */
    public function reconcile(): array
    {
        $stats = ['expired' => 0, 'orphaned' => 0];

        // 1. หมดอายุรายการ deposit ที่เกินเวลา
        $expired = Deposit::where('method', 'sms_auto')
            ->where('status', SmsDepositStatus::WaitingTransfer->value)
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expired as $deposit) {
            $deposit->update(['status' => SmsDepositStatus::Expired->value]);
            $stats['expired']++;
        }

        // 2. หา SMS notifications ที่ไม่มีคู่ (orphaned)
        $orphanedNotifications = SmsPaymentNotification::where('status', 'pending')
            ->where('type', 'credit')
            ->where('created_at', '<', now()->subHours(1))
            ->count();

        $stats['orphaned'] = $orphanedNotifications;

        // 3. Cleanup expired unique amounts
        UniquePaymentAmount::where('status', 'reserved')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        // 4. Cleanup old nonces
        DB::table('sms_payment_nonces')
            ->where('used_at', '<', now()->subHours(config('smschecker.nonce_expiry_hours', 24)))
            ->delete();

        Log::info('SMS Deposit reconcile completed', $stats);

        return $stats;
    }

    /**
     * Admin: Dashboard stats
     */
    public function getDashboardStats(): array
    {
        $cacheKey = 'sms_deposit_dashboard';

        return Cache::remember($cacheKey, 30, function () {
            $today = now()->startOfDay();

            return [
                'today' => [
                    'total_deposits' => Deposit::where('method', 'sms_auto')
                        ->where('created_at', '>=', $today)
                        ->count(),
                    'total_credited' => Deposit::where('method', 'sms_auto')
                        ->where('status', SmsDepositStatus::Credited->value)
                        ->where('created_at', '>=', $today)
                        ->sum('amount'),
                    'success_rate' => $this->calculateSuccessRate($today),
                    'avg_match_time' => $this->calculateAvgMatchTime($today),
                    'pending_count' => Deposit::where('method', 'sms_auto')
                        ->where('status', SmsDepositStatus::WaitingTransfer->value)
                        ->where('expires_at', '>', now())
                        ->count(),
                    'expired_count' => Deposit::where('method', 'sms_auto')
                        ->where('status', SmsDepositStatus::Expired->value)
                        ->where('created_at', '>=', $today)
                        ->count(),
                ],
                'devices' => [
                    'total' => SmsCheckerDevice::count(),
                    'active' => SmsCheckerDevice::where('status', 'active')
                        ->where('last_active_at', '>=', now()->subMinutes(5))
                        ->count(),
                    'inactive' => SmsCheckerDevice::where('status', 'active')
                        ->where(function ($q) {
                            $q->whereNull('last_active_at')
                                ->orWhere('last_active_at', '<', now()->subMinutes(5));
                        })
                        ->count(),
                ],
                'unmatched_sms' => SmsPaymentNotification::where('status', 'pending')
                    ->where('type', 'credit')
                    ->count(),
                'recent_notifications' => SmsPaymentNotification::orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get(['id', 'bank', 'type', 'amount', 'status', 'created_at']),
            ];
        });
    }

    /**
     * Admin: Manual match - จับคู่ SMS กับ Deposit ด้วยมือ
     */
    public function manualMatch(int $notificationId, int $depositId, int $adminId): bool
    {
        return DB::transaction(function () use ($notificationId, $depositId, $adminId) {
            $notification = SmsPaymentNotification::findOrFail($notificationId);
            $deposit = Deposit::findOrFail($depositId);

            if ($notification->status !== 'pending') {
                throw new \RuntimeException('SMS notification ถูกจับคู่แล้ว');
            }

            if (!in_array($deposit->status, [SmsDepositStatus::WaitingTransfer->value, SmsDepositStatus::Expired->value])) {
                throw new \RuntimeException('รายการฝากไม่สามารถจับคู่ได้');
            }

            // อัพเดท notification
            $notification->update([
                'status' => 'matched',
                'matched_transaction_id' => $deposit->id,
            ]);

            // อัพเดท deposit
            $deposit->update([
                'status' => SmsDepositStatus::Matched->value,
                'matched_at' => now(),
                'sms_notification_id' => $notification->id,
                'matched_bank' => $notification->bank,
                'matched_reference' => $notification->reference_number,
                'manual_matched_by' => $adminId,
            ]);

            // เติมเงินเข้ากระเป๋า
            $user = User::findOrFail($deposit->user_id);
            $this->balanceService->credit(
                user: $user,
                amount: $deposit->amount,
                description: "ฝากเงิน (Manual Match โดย Admin) #{$deposit->id}",
                type: TransactionType::Deposit,
                referenceId: $deposit->id,
                referenceType: 'deposit',
            );

            $deposit->update([
                'status' => SmsDepositStatus::Credited->value,
                'credited_at' => now(),
            ]);

            $this->updateUserDepositStats($user, $deposit->amount);

            event(new DepositCredited($deposit, $user));

            Log::info('SMS Deposit manual match', [
                'admin_id' => $adminId,
                'deposit_id' => $deposit->id,
                'notification_id' => $notification->id,
                'amount' => $deposit->amount,
            ]);

            return true;
        });
    }

    // ─────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────

    /**
     * Validate deposit limits
     */
    private function validateDepositLimits(User $user, float $amount): void
    {
        $limits = config('smschecker.deposit_limits');

        if ($amount < $limits['min_amount']) {
            throw new \InvalidArgumentException("ยอดฝากขั้นต่ำ ฿" . number_format($limits['min_amount'], 2));
        }

        if ($amount > $limits['max_amount']) {
            throw new \InvalidArgumentException("ยอดฝากสูงสุด ฿" . number_format($limits['max_amount'], 2));
        }

        // เช็คยอดฝากวันนี้
        $todayTotal = Deposit::where('user_id', $user->id)
            ->where('method', 'sms_auto')
            ->whereIn('status', [
                SmsDepositStatus::WaitingTransfer->value,
                SmsDepositStatus::Matching->value,
                SmsDepositStatus::Matched->value,
                SmsDepositStatus::Credited->value,
            ])
            ->where('created_at', '>=', now()->startOfDay())
            ->sum('amount');

        if (($todayTotal + $amount) > $limits['daily_limit']) {
            $remaining = max(0, $limits['daily_limit'] - $todayTotal);
            throw new \InvalidArgumentException(
                "เกินยอดฝากรายวัน (ฝากได้อีก ฿" . number_format($remaining, 2) . ")"
            );
        }

        // เช็ครายการ pending ที่มีอยู่ (ป้องกัน spam)
        $pendingCount = Deposit::where('user_id', $user->id)
            ->where('method', 'sms_auto')
            ->where('status', SmsDepositStatus::WaitingTransfer->value)
            ->where('expires_at', '>', now())
            ->count();

        if ($pendingCount >= 3) {
            throw new \InvalidArgumentException('มีรายการฝากที่รอดำเนินการอยู่ 3 รายการ กรุณารอให้เสร็จก่อน');
        }
    }

    /**
     * Generate PromptPay QR data string (EMVCo format)
     */
    private function generatePromptPayQR(string $promptpayNumber, float $amount): string
    {
        // PromptPay QR follows EMVCo standard
        // This generates the data string for QR code rendering
        $cleanNumber = preg_replace('/[^0-9]/', '', $promptpayNumber);

        // Format: 00020101021129370016A000000677010111 + [ID type + number] + ...
        // Simplified - actual implementation uses a dedicated library
        $payload = [
            'format_indicator' => '000201',
            'qr_type' => '010212', // dynamic QR
            'merchant_id' => '2937',
            'promptpay_id' => $cleanNumber,
            'country' => 'TH',
            'currency' => '764', // THB
            'amount' => number_format($amount, 2, '.', ''),
        ];

        // In production, use a PromptPay QR library like:
        // - phattarachai/promptpay-qr
        // - kittinan/php-promptpay-qr
        return json_encode($payload);
    }

    /**
     * อัพเดทสถิติ deposit ใน user risk profile
     */
    private function updateUserDepositStats(User $user, float $amount): void
    {
        try {
            $profile = $user->riskProfile;

            if ($profile) {
                $profile->increment('total_deposit', $amount);
                $profile->increment('net_profit_for_system', $amount);
            }

            // อัพเดท daily stats
            DB::table('user_daily_stats')
                ->updateOrInsert(
                    [
                        'user_id' => $user->id,
                        'stat_date' => now()->toDateString(),
                    ],
                    [
                        'deposit_amount' => DB::raw("deposit_amount + {$amount}"),
                        'updated_at' => now(),
                    ]
                );

            // อัพเดท system real-time stats
            DB::table('system_realtime_stats')
                ->where('stat_key', 'today_total_deposit')
                ->update(['stat_value' => DB::raw("stat_value + {$amount}")]);

        } catch (\Exception $e) {
            Log::warning('Failed to update deposit stats', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * คำนวณอัตราจับคู่สำเร็จ
     */
    private function calculateSuccessRate(\Carbon\Carbon $since): float
    {
        $total = Deposit::where('method', 'sms_auto')
            ->where('created_at', '>=', $since)
            ->count();

        if ($total === 0) return 0;

        $credited = Deposit::where('method', 'sms_auto')
            ->where('status', SmsDepositStatus::Credited->value)
            ->where('created_at', '>=', $since)
            ->count();

        return round(($credited / $total) * 100, 1);
    }

    /**
     * คำนวณเวลาเฉลี่ยในการจับคู่ (วินาที)
     */
    private function calculateAvgMatchTime(\Carbon\Carbon $since): ?float
    {
        $avg = Deposit::where('method', 'sms_auto')
            ->where('status', SmsDepositStatus::Credited->value)
            ->where('created_at', '>=', $since)
            ->whereNotNull('matched_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, matched_at)) as avg_seconds')
            ->value('avg_seconds');

        return $avg ? round((float) $avg, 1) : null;
    }
}
