<?php

namespace App\Services\Deposit;

use App\Models\SmsCheckerDevice;
use App\Models\SmsPaymentNotification;
use App\Models\UniquePaymentAmount;
use App\Events\DepositMatched;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service จัดการ SMS notifications จาก smschecker Android app
 *
 * ทำหน้าที่:
 * 1. รับ & ถอดรหัส SMS จากอุปกรณ์
 * 2. จับคู่ยอดเงินกับ Deposit ที่รอ
 * 3. Trigger การเติมเงินเข้ากระเป๋า
 */
class SmsPaymentProcessorService
{
    /**
     * รับ SMS notification จากอุปกรณ์ Android
     *
     * @param array $payload Decrypted payload
     * @param SmsCheckerDevice $device Authenticated device
     * @param string $ipAddress Client IP
     * @return array Result
     */
    public function processNotification(array $payload, SmsCheckerDevice $device, string $ipAddress): array
    {
        return DB::transaction(function () use ($payload, $device, $ipAddress) {
            // 1. ตรวจสอบ nonce ซ้ำ (replay attack prevention)
            $existingNonce = DB::table('sms_payment_nonces')
                ->where('nonce', $payload['nonce'])
                ->exists();

            if ($existingNonce) {
                Log::warning('SMS Payment: Duplicate nonce', [
                    'nonce' => $payload['nonce'],
                    'device_id' => $device->device_id,
                ]);
                return [
                    'success' => false,
                    'message' => 'Duplicate request (nonce already used)',
                ];
            }

            // 2. บันทึก nonce
            DB::table('sms_payment_nonces')->insert([
                'nonce' => $payload['nonce'],
                'device_id' => $device->device_id,
                'used_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. สร้าง notification record
            $notification = SmsPaymentNotification::create([
                'bank' => $payload['bank'],
                'type' => $payload['type'],
                'amount' => $payload['amount'],
                'account_number' => $payload['account_number'] ?? '',
                'sender_or_receiver' => $payload['sender_or_receiver'] ?? '',
                'reference_number' => $payload['reference_number'] ?? '',
                'sms_timestamp' => date('Y-m-d H:i:s', $payload['sms_timestamp'] / 1000),
                'device_id' => $device->device_id,
                'nonce' => $payload['nonce'],
                'status' => 'pending',
                'raw_payload' => json_encode($payload),
                'ip_address' => $ipAddress,
            ]);

            // 4. อัพเดท device activity
            $device->update([
                'last_active_at' => now(),
                'ip_address' => $ipAddress,
            ]);

            // 5. ถ้าเป็นเงินเข้า (credit) → พยายามจับคู่
            $matched = false;
            if ($notification->type === 'credit') {
                $matched = $this->attemptMatch($notification);
            }

            Log::info('SMS Payment processed', [
                'notification_id' => $notification->id,
                'bank' => $notification->bank,
                'type' => $notification->type,
                'amount' => $notification->amount,
                'matched' => $matched,
            ]);

            return [
                'success' => true,
                'message' => $matched ? 'Payment matched and confirmed' : 'Notification recorded',
                'data' => [
                    'notification_id' => $notification->id,
                    'status' => $notification->fresh()->status,
                    'matched' => $matched,
                ],
            ];
        });
    }

    /**
     * พยายามจับคู่ SMS กับ Deposit ที่รอ
     * ใช้ unique decimal amount matching
     */
    public function attemptMatch(SmsPaymentNotification $notification): bool
    {
        if ($notification->type !== 'credit') {
            return false;
        }

        // Strategy 1: จับคู่ด้วย unique amount
        $uniqueAmount = UniquePaymentAmount::where('unique_amount', $notification->amount)
            ->where('status', 'reserved')
            ->where('expires_at', '>', now())
            ->lockForUpdate()
            ->first();

        if ($uniqueAmount) {
            return $this->confirmMatch($notification, $uniqueAmount);
        }

        // Strategy 2: จับคู่ด้วย reference number (PromptPay)
        if ($notification->reference_number) {
            $uniqueAmount = UniquePaymentAmount::where('status', 'reserved')
                ->where('expires_at', '>', now())
                ->whereRaw('ABS(unique_amount - ?) < 0.01', [$notification->amount])
                ->lockForUpdate()
                ->first();

            if ($uniqueAmount) {
                return $this->confirmMatch($notification, $uniqueAmount);
            }
        }

        // Strategy 3: Fuzzy match สำหรับกรณียอดตรงเป๊ะ (ไม่มีทศนิยม)
        // เช่น ลูกค้าโอน 500.00 แทน 500.37
        // → ไม่จับคู่อัตโนมัติ → ส่งไปให้ Admin manual match

        Log::info('SMS Payment: No auto-match found', [
            'notification_id' => $notification->id,
            'amount' => $notification->amount,
        ]);

        // Broadcast สำหรับ Admin dashboard
        event(new DepositMatched(null, $notification, false));

        return false;
    }

    /**
     * ยืนยันการจับคู่และ trigger เติมเงิน
     */
    private function confirmMatch(SmsPaymentNotification $notification, UniquePaymentAmount $uniqueAmount): bool
    {
        // อัพเดท notification
        $notification->update([
            'status' => 'matched',
            'matched_transaction_id' => $uniqueAmount->transaction_id,
        ]);

        // อัพเดท unique amount
        $uniqueAmount->update([
            'status' => 'used',
            'matched_at' => now(),
        ]);

        // เติมเงินผ่าน SmsDepositService
        $depositService = app(SmsDepositService::class);
        $credited = $depositService->handleSmsMatch($notification, $uniqueAmount);

        if ($credited) {
            $notification->update(['status' => 'confirmed']);
        }

        // Broadcast success
        event(new DepositMatched(
            $uniqueAmount->transaction_id,
            $notification,
            $credited,
        ));

        return $credited;
    }

    /**
     * ถอดรหัส AES-256-GCM payload จาก Android app
     */
    public function decryptPayload(string $encryptedData, string $secretKey): ?array
    {
        try {
            $combined = base64_decode($encryptedData);
            if ($combined === false || strlen($combined) < 28) { // 12 IV + 16 tag minimum
                return null;
            }

            $ivLength = 12;  // GCM IV = 12 bytes
            $tagLength = 16; // GCM tag = 16 bytes

            $iv = substr($combined, 0, $ivLength);
            $cipherTextWithTag = substr($combined, $ivLength);

            // แยก ciphertext กับ tag
            $tag = substr($cipherTextWithTag, -$tagLength);
            $cipherText = substr($cipherTextWithTag, 0, -$tagLength);

            // Derive key (first 32 bytes of secret)
            $key = str_pad(substr($secretKey, 0, 32), 32, "\0");

            $decrypted = openssl_decrypt(
                $cipherText,
                'aes-256-gcm',
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );

            if ($decrypted === false) {
                Log::warning('SMS Payment: Decryption failed');
                return null;
            }

            $payload = json_decode($decrypted, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('SMS Payment: Invalid JSON in decrypted payload');
                return null;
            }

            return $payload;
        } catch (\Exception $e) {
            Log::error('SMS Payment: Decryption error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * ตรวจสอบ HMAC-SHA256 signature
     */
    public function verifySignature(string $data, string $signature, string $secretKey): bool
    {
        $expected = base64_encode(hash_hmac('sha256', $data, $secretKey, true));
        return hash_equals($expected, $signature);
    }

    /**
     * Cleanup expired data (เรียกจาก Scheduler)
     */
    public function cleanup(): void
    {
        // 1. Expire old unique amounts
        UniquePaymentAmount::where('status', 'reserved')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        // 2. Clean old nonces
        $nonceExpiry = config('smschecker.nonce_expiry_hours', 24);
        DB::table('sms_payment_nonces')
            ->where('used_at', '<', now()->subHours($nonceExpiry))
            ->delete();

        // 3. Expire old pending notifications (7 days)
        SmsPaymentNotification::where('status', 'pending')
            ->where('created_at', '<', now()->subDays(7))
            ->update(['status' => 'expired']);

        Log::info('SMS Payment cleanup completed');
    }
}
