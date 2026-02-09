<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * GET /admin/settings
     */
    public function index(): JsonResponse
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * PUT /admin/settings
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
            'settings.*.group' => 'nullable|string',
            'settings.*.type' => 'nullable|string|in:string,integer,boolean,json,text',
        ]);

        foreach ($request->settings as $setting) {
            Setting::setValue(
                $setting['key'],
                $setting['value'],
                $setting['group'] ?? 'general',
                $setting['type'] ?? 'string',
            );
        }

        AdminLog::log($request->user()->id, 'update_settings', 'อัปเดตการตั้งค่าระบบ');

        return response()->json([
            'success' => true,
            'message' => 'บันทึกการตั้งค่าสำเร็จ',
        ]);
    }

    /**
     * GET /admin/settings/sms
     */
    public function sms(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Setting::getByGroup('sms'),
        ]);
    }

    /**
     * GET /admin/settings/payment
     */
    public function payment(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Setting::getByGroup('payment'),
        ]);
    }

    /**
     * GET /admin/settings/announcement
     */
    public function announcement(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'marquee_text' => Setting::getValue('marquee_text', ''),
                'announcement' => Setting::getValue('announcement', ''),
                'maintenance_mode' => Setting::getValue('maintenance_mode', false),
            ],
        ]);
    }

    /**
     * GET /admin/logs
     */
    public function logs(Request $request): JsonResponse
    {
        $logs = AdminLog::with('admin:id,name')
            ->orderBy('created_at', 'desc');

        if ($action = $request->string('action')->value()) {
            $logs->where('action', $action);
        }

        if ($adminId = $request->integer('admin_id')) {
            $logs->where('admin_id', $adminId);
        }

        return response()->json([
            'success' => true,
            'data' => $logs->paginate(50),
        ]);
    }
}
