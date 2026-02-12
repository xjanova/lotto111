<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Services\DemoModeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DemoController extends Controller
{
    public function __construct(private DemoModeService $demoService) {}

    /**
     * POST /admin/demo/activate
     */
    public function activate(Request $request): JsonResponse
    {
        if ($this->demoService->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Demo mode is already active',
            ]);
        }

        $result = $this->demoService->activate();

        AdminLog::log(
            $request->user()?->id,
            'demo_activate',
            'เปิดโหมดสาธิต (Demo Mode) — สร้างข้อมูลจำลอง ' . ($result['counts']['users'] ?? 0) . ' users'
        );

        return response()->json([
            'success' => true,
            'message' => 'เปิดโหมดสาธิตสำเร็จ',
            'data' => $result,
        ]);
    }

    /**
     * POST /admin/demo/deactivate
     */
    public function deactivate(Request $request): JsonResponse
    {
        if (!$this->demoService->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Demo mode is not active',
            ]);
        }

        $result = $this->demoService->deactivate();

        AdminLog::log(
            $request->user()?->id,
            'demo_deactivate',
            'ปิดโหมดสาธิต (Demo Mode) — ลบข้อมูลจำลองทั้งหมด'
        );

        return response()->json([
            'success' => true,
            'message' => 'ปิดโหมดสาธิตสำเร็จ ข้อมูลจำลองถูกลบแล้ว',
            'data' => $result,
        ]);
    }

    /**
     * POST /admin/demo/refresh
     */
    public function refresh(Request $request): JsonResponse
    {
        $result = $this->demoService->refresh();

        AdminLog::log(
            $request->user()?->id,
            'demo_refresh',
            'รีเฟรชข้อมูลจำลอง (Demo Mode)'
        );

        return response()->json([
            'success' => true,
            'message' => 'รีเฟรชข้อมูลจำลองสำเร็จ',
            'data' => $result,
        ]);
    }

    /**
     * GET /admin/demo/status
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'is_active' => $this->demoService->isActive(),
                'counts' => $this->demoService->isActive() ? $this->demoService->getCounts() : null,
            ],
        ]);
    }
}
