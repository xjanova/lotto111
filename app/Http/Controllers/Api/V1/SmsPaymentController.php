<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SmsCheckerDevice;
use App\Services\Deposit\SmsPaymentProcessorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * API Controller สำหรับรับ SMS notifications จาก smschecker Android app
 *
 * ทุก endpoint ต้องผ่าน VerifySmsCheckerDevice middleware
 * ที่ตรวจสอบ X-Api-Key header
 */
class SmsPaymentController extends Controller
{
    public function __construct(
        private SmsPaymentProcessorService $smsProcessor,
    ) {}

    /**
     * รับ SMS payment notification จาก Android app
     *
     * POST /api/v1/sms-payment/notify
     *
     * Headers:
     *   X-Api-Key: device api key
     *   X-Signature: HMAC-SHA256 signature
     *   X-Nonce: unique nonce
     *   X-Timestamp: request timestamp (ms)
     *
     * Body:
     *   data: AES-256-GCM encrypted payload (base64)
     */
    public function notify(Request $request): JsonResponse
    {
        $device = $request->attributes->get('sms_checker_device');
        if (!$device instanceof SmsCheckerDevice) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // ตรวจสอบ security headers
        $signature = $request->header('X-Signature');
        $nonce = $request->header('X-Nonce');
        $timestamp = $request->header('X-Timestamp');

        if (!$signature || !$nonce || !$timestamp) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required security headers (X-Signature, X-Nonce, X-Timestamp)',
            ], 400);
        }

        // ตรวจสอบ timestamp freshness
        $tolerance = config('smschecker.timestamp_tolerance', 300) * 1000; // convert to ms
        $requestTime = intval($timestamp);
        $currentTime = intval(round(microtime(true) * 1000));

        if (abs($currentTime - $requestTime) > $tolerance) {
            return response()->json([
                'success' => false,
                'message' => 'Request timestamp expired',
            ], 400);
        }

        // ดึง encrypted data
        $encryptedData = $request->input('data');
        if (!$encryptedData) {
            return response()->json([
                'success' => false,
                'message' => 'No payload data provided',
            ], 400);
        }

        // ตรวจสอบ HMAC signature
        $signatureData = $encryptedData . $nonce . $timestamp;
        if (!$this->smsProcessor->verifySignature($signatureData, $signature, $device->secret_key)) {
            Log::warning('SMS Payment: Invalid signature', [
                'device_id' => $device->device_id,
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 401);
        }

        // ถอดรหัส payload
        $payload = $this->smsProcessor->decryptPayload($encryptedData, $device->secret_key);
        if (!$payload) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to decrypt payload',
            ], 400);
        }

        // Validate payload fields
        $validator = Validator::make($payload, [
            'bank' => 'required|string|max:20',
            'type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0.01',
            'account_number' => 'nullable|string|max:50',
            'sender_or_receiver' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'sms_timestamp' => 'required|numeric',
            'device_id' => 'required|string',
            'nonce' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payload data',
                'errors' => $validator->errors(),
            ], 422);
        }

        // ประมวลผล
        $result = $this->smsProcessor->processNotification(
            $payload,
            $device,
            $request->ip()
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * เช็คสถานะอุปกรณ์
     *
     * GET /api/v1/sms-payment/status
     */
    public function status(Request $request): JsonResponse
    {
        $device = $request->attributes->get('sms_checker_device');
        if (!$device instanceof SmsCheckerDevice) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $device->update(['last_active_at' => now()]);

        return response()->json([
            'success' => true,
            'status' => $device->status,
            'server_time' => now()->toIso8601String(),
            'message' => null,
        ]);
    }

    /**
     * ลงทะเบียนอุปกรณ์
     *
     * POST /api/v1/sms-payment/register-device
     */
    public function registerDevice(Request $request): JsonResponse
    {
        $device = $request->attributes->get('sms_checker_device');
        if (!$device instanceof SmsCheckerDevice) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|max:50',
            'device_name' => 'required|string|max:100',
            'platform' => 'required|string|max:20',
            'app_version' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $device->update([
            'device_name' => $request->input('device_name'),
            'platform' => $request->input('platform'),
            'app_version' => $request->input('app_version'),
            'last_active_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully',
        ]);
    }
}
