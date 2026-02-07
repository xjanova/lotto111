<?php

namespace App\Http\Middleware;

use App\Models\SmsCheckerDevice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware สำหรับตรวจสอบ SMS Checker device
 *
 * ใช้กับ routes ที่รับข้อมูลจาก smschecker Android app
 * ตรวจสอบ API key และสถานะอุปกรณ์
 */
class VerifySmsCheckerDevice
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Api-Key');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required (X-Api-Key header)',
            ], 401);
        }

        // ค้นหาอุปกรณ์จาก API key
        $device = SmsCheckerDevice::findByApiKey($apiKey);

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
            ], 401);
        }

        // ตรวจสอบสถานะอุปกรณ์
        if (!$device->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Device is ' . $device->status,
            ], 403);
        }

        // ตรวจสอบ Device ID (ถ้ามี)
        $deviceId = $request->header('X-Device-Id');
        if ($deviceId && $device->device_id !== $deviceId) {
            return response()->json([
                'success' => false,
                'message' => 'Device ID mismatch',
            ], 403);
        }

        // Rate limiting per device
        $rateLimitKey = 'sms_device_rate:' . $device->device_id;
        $maxPerMinute = config('smschecker.rate_limit_per_minute', 30);

        $currentCount = cache()->get($rateLimitKey, 0);
        if ($currentCount >= $maxPerMinute) {
            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded',
            ], 429);
        }
        cache()->put($rateLimitKey, $currentCount + 1, 60);

        // Attach device to request attributes
        $request->attributes->set('sms_checker_device', $device);

        return $next($request);
    }
}
