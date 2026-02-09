<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsCheckerDevice;
use App\Models\SmsPaymentNotification;
use App\Models\Deposit;
use App\Services\Deposit\SmsDepositService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Admin Controller สำหรับจัดการ SMS Deposit System
 *
 * Features:
 * - Dashboard stats
 * - Device management (CRUD)
 * - SMS notification monitoring
 * - Manual matching
 * - QR code generation
 */
class SmsDepositController extends Controller
{
    public function __construct(
        private SmsDepositService $depositService,
    ) {}

    // ─────────────────────────────────────────
    // Dashboard
    // ─────────────────────────────────────────

    /**
     * SMS Deposit Dashboard stats
     */
    public function dashboard(Request $request): View|JsonResponse
    {
        $stats = $this->depositService->getDashboardStats();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        }

        $devices = SmsCheckerDevice::orderBy('created_at', 'desc')->get();
        $pendingCount = Deposit::where('method', 'sms_auto')->where('status', 'waiting_transfer')->count();

        return view('admin.sms-deposit.dashboard', compact('stats', 'devices', 'pendingCount'));
    }

    // ─────────────────────────────────────────
    // Device Management
    // ─────────────────────────────────────────

    /**
     * รายการอุปกรณ์ทั้งหมด
     */
    public function devices(): JsonResponse
    {
        $devices = SmsCheckerDevice::orderBy('created_at', 'desc')
            ->get()
            ->map(function ($device) {
                return [
                    'id' => $device->id,
                    'device_id' => $device->device_id,
                    'name' => $device->name,
                    'platform' => $device->platform,
                    'app_version' => $device->app_version,
                    'status' => $device->status,
                    'is_online' => $device->last_active_at && $device->last_active_at->gt(now()->subMinutes(5)),
                    'last_active_at' => $device->last_active_at?->toIso8601String(),
                    'ip_address' => $device->ip_address,
                    'notification_count' => $device->notifications()->count(),
                    'created_at' => $device->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $devices,
        ]);
    }

    /**
     * สร้างอุปกรณ์ใหม่
     */
    public function createDevice(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $device = SmsCheckerDevice::create([
            'device_id' => 'DEV-' . strtoupper(bin2hex(random_bytes(6))),
            'name' => $request->input('name'),
            'api_key' => SmsCheckerDevice::generateApiKey(),
            'secret_key' => SmsCheckerDevice::generateSecretKey(),
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'อุปกรณ์ถูกสร้างเรียบร้อย',
            'data' => [
                'id' => $device->id,
                'device_id' => $device->device_id,
                'name' => $device->name,
                'api_key' => $device->api_key,
                'secret_key' => $device->secret_key,
                'qr_config' => $this->generateQrConfig($device),
            ],
        ]);
    }

    /**
     * อัพเดทสถานะอุปกรณ์
     */
    public function updateDevice(Request $request, int $id): JsonResponse
    {
        $device = SmsCheckerDevice::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100',
            'status' => 'sometimes|in:active,inactive,blocked',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $device->update($request->only(['name', 'status']));

        return response()->json([
            'success' => true,
            'message' => 'อัพเดทอุปกรณ์เรียบร้อย',
        ]);
    }

    /**
     * Regenerate API key & secret key ของอุปกรณ์
     */
    public function regenerateKeys(int $id): JsonResponse
    {
        $device = SmsCheckerDevice::findOrFail($id);

        $device->update([
            'api_key' => SmsCheckerDevice::generateApiKey(),
            'secret_key' => SmsCheckerDevice::generateSecretKey(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'สร้าง Key ใหม่เรียบร้อย กรุณา scan QR ใหม่ที่อุปกรณ์',
            'data' => [
                'api_key' => $device->api_key,
                'secret_key' => $device->secret_key,
                'qr_config' => $this->generateQrConfig($device),
            ],
        ]);
    }

    /**
     * สร้าง QR Code config สำหรับ setup อุปกรณ์
     */
    public function getDeviceQr(int $id): JsonResponse
    {
        $device = SmsCheckerDevice::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->generateQrConfig($device),
        ]);
    }

    /**
     * ลบอุปกรณ์
     */
    public function deleteDevice(int $id): JsonResponse
    {
        $device = SmsCheckerDevice::findOrFail($id);
        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบอุปกรณ์เรียบร้อย',
        ]);
    }

    // ─────────────────────────────────────────
    // SMS Notifications
    // ─────────────────────────────────────────

    /**
     * รายการ SMS notifications
     */
    public function notifications(Request $request): JsonResponse
    {
        $query = SmsPaymentNotification::orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('bank')) {
            $query->where('bank', $request->input('bank'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to'));
        }

        $notifications = $query->paginate($request->input('per_page', 30));

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Pending deposits (รายการที่รอจับคู่)
     */
    public function pendingDeposits(): JsonResponse
    {
        $pending = Deposit::where('method', 'sms_auto')
            ->where('status', 'waiting_transfer')
            ->where('expires_at', '>', now())
            ->with('user:id,name,phone')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pending,
        ]);
    }

    /**
     * Manual match - จับคู่ SMS กับ Deposit ด้วยมือ
     */
    public function manualMatch(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'notification_id' => 'required|integer|exists:sms_payment_notifications,id',
            'deposit_id' => 'required|integer|exists:deposits,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->depositService->manualMatch(
                notificationId: $request->input('notification_id'),
                depositId: $request->input('deposit_id'),
                adminId: $request->user()->id,
            );

            return response()->json([
                'success' => true,
                'message' => 'จับคู่และเติมเงินเรียบร้อย',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ─────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────

    /**
     * สร้าง QR config JSON สำหรับ smschecker Android app
     */
    private function generateQrConfig(SmsCheckerDevice $device): array
    {
        return [
            'type' => 'smschecker_config',
            'version' => 1,
            'url' => rtrim(config('app.url'), '/'),
            'apiKey' => $device->api_key,
            'secretKey' => $device->secret_key,
            'deviceName' => $device->name,
        ];
    }
}
