<?php

namespace App\Services;

use App\Models\OtpVerification;
use Illuminate\Support\Facades\Cache;

class OtpService
{
    public function send(string $phone, string $purpose): array
    {
        // Rate limiting check
        $rateLimitKey = "otp:rate:{$phone}";
        $sentCount = (int) Cache::get($rateLimitKey, 0);
        $maxPerHour = config('sms.otp.rate_limit', 3);

        if ($sentCount >= $maxPerHour) {
            return [
                'success' => false,
                'message' => 'ส่ง OTP เกินจำนวนที่กำหนด กรุณารอสักครู่',
                'retry_after' => 3600,
            ];
        }

        // Cooldown check
        $cooldownKey = "otp:cooldown:{$phone}";
        if (Cache::has($cooldownKey)) {
            $remaining = Cache::get($cooldownKey) - time();
            return [
                'success' => false,
                'message' => 'กรุณารอก่อนส่ง OTP อีกครั้ง',
                'retry_after' => max(0, $remaining),
            ];
        }

        $otpCode = $this->generateCode();
        $expiryMinutes = config('sms.otp.expiry_minutes', 5);

        $otp = OtpVerification::create([
            'phone' => $phone,
            'otp_code' => $otpCode,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);

        // Send via SMS service
        $smsService = app(SmsService::class);
        $message = str_replace(
            [':code', ':minutes'],
            [$otpCode, $expiryMinutes],
            config('sms.otp.message_template', 'รหัส OTP: :code (หมดอายุ :minutes นาที)')
        );
        $smsService->send($phone, $message);

        // Set rate limit & cooldown
        Cache::put($rateLimitKey, $sentCount + 1, 3600);
        Cache::put($cooldownKey, time() + config('sms.otp.cooldown_seconds', 60), config('sms.otp.cooldown_seconds', 60));

        return [
            'success' => true,
            'message' => 'ส่ง OTP สำเร็จ',
            'expires_in' => $expiryMinutes * 60,
        ];
    }

    public function verify(string $phone, string $code, string $purpose): array
    {
        $otp = OtpVerification::where('phone', $phone)
            ->where('purpose', $purpose)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();

        if (! $otp) {
            return [
                'success' => false,
                'message' => 'ไม่พบ OTP หรือ OTP หมดอายุแล้ว',
            ];
        }

        if ($otp->attempts >= config('sms.otp.max_attempts', 5)) {
            return [
                'success' => false,
                'message' => 'ลองผิดเกินจำนวนที่กำหนด กรุณาขอ OTP ใหม่',
            ];
        }

        if ($otp->otp_code !== $code) {
            $otp->incrementAttempts();
            return [
                'success' => false,
                'message' => 'รหัส OTP ไม่ถูกต้อง',
                'remaining_attempts' => config('sms.otp.max_attempts', 5) - $otp->attempts,
            ];
        }

        $otp->markUsed();

        return [
            'success' => true,
            'message' => 'ยืนยัน OTP สำเร็จ',
        ];
    }

    private function generateCode(): string
    {
        $length = config('sms.otp.length', 6);
        return str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
    }
}
