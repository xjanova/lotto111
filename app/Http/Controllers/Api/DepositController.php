<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Deposit\SmsDepositService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * API Controller สำหรับฝากเงินฝั่งลูกค้า
 *
 * ต้องผ่าน auth:sanctum middleware
 */
class DepositController extends Controller
{
    public function __construct(
        private SmsDepositService $depositService,
    ) {}

    /**
     * สร้างรายการฝากเงินอัตโนมัติ (SMS)
     *
     * POST /api/deposit/sms
     */
    public function createSmsDeposit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->depositService->createDeposit(
                user: $request->user(),
                amount: (float) $request->input('amount'),
            );

            return response()->json([
                'success' => true,
                'message' => 'กรุณาโอนเงินตามยอดที่แสดง',
                'data' => $result,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 503);
        }
    }

    /**
     * เช็คสถานะ deposit real-time
     *
     * GET /api/deposit/{id}/status
     */
    public function getStatus(Request $request, int $id): JsonResponse
    {
        $result = $this->depositService->getDepositStatus($id, $request->user());

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบรายการฝากเงิน',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * ยกเลิกรายการฝาก
     *
     * POST /api/deposit/{id}/cancel
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $cancelled = $this->depositService->cancelDeposit($id, $request->user());

        if (!$cancelled) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถยกเลิกรายการได้',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'ยกเลิกรายการฝากเงินเรียบร้อย',
        ]);
    }

    /**
     * ประวัติฝากเงิน
     *
     * GET /api/deposit/history
     */
    public function history(Request $request): JsonResponse
    {
        $history = $this->depositService->getDepositHistory(
            user: $request->user(),
            perPage: (int) $request->input('per_page', 20),
        );

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * ข้อมูลบัญชีรับเงินและ limits
     *
     * GET /api/deposit/info
     */
    public function info(): JsonResponse
    {
        $account = config('smschecker.receiving_account');
        $limits = config('smschecker.deposit_limits');

        return response()->json([
            'success' => true,
            'data' => [
                'bank' => [
                    'name' => $account['bank_name'],
                    'account_number' => $account['account_number'],
                    'account_name' => $account['account_name'],
                ],
                'promptpay' => [
                    'number' => $account['promptpay_number'],
                ],
                'limits' => [
                    'min_amount' => $limits['min_amount'],
                    'max_amount' => $limits['max_amount'],
                    'daily_limit' => $limits['daily_limit'],
                ],
                'supported_banks' => config('smschecker.supported_banks'),
            ],
        ]);
    }
}
