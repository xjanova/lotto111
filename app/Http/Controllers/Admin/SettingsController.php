<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * GET /admin/settings
     */
    public function index(Request $request): View|JsonResponse
    {
        if ($request->wantsJson()) {
            $settings = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');

            return response()->json([
                'success' => true,
                'data' => $settings,
            ]);
        }

        $settings = Setting::all()->mapWithKeys(fn ($s) => [$s->key => $s->getTypedValue()])->toArray();

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * PUT /admin/settings
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        $settingsData = $request->settings;
        $group = $request->input('group', 'general');

        // Support both formats:
        // 1. New format from UI: { group: "general", settings: { key: value, ... } }
        // 2. Old API format: { settings: [ { key: "...", value: "..." }, ... ] }
        if (isset($settingsData[0]) && is_array($settingsData[0]) && isset($settingsData[0]['key'])) {
            // Old format: array of { key, value, group?, type? }
            foreach ($settingsData as $setting) {
                Setting::setValue(
                    $setting['key'],
                    $setting['value'],
                    $setting['group'] ?? 'general',
                    $setting['type'] ?? 'string',
                );
            }
        } else {
            // New format: flat key-value object
            foreach ($settingsData as $key => $value) {
                if (! is_string($key)) {
                    continue;
                }
                $existing = Setting::where('key', $key)->first();
                if ($existing) {
                    $storeValue = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
                    $existing->update(['value' => $storeValue]);
                    \Illuminate\Support\Facades\Cache::forget("settings:{$key}");
                } else {
                    Setting::setValue($key, $value, $group, 'string');
                }
            }
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
    public function logs(Request $request): View|JsonResponse
    {
        $query = AdminLog::with('admin:id,name')
            ->orderBy('created_at', 'desc');

        if ($action = $request->string('action')->value()) {
            $query->where('action', $action);
        }

        if ($adminId = $request->integer('admin_id')) {
            $query->where('admin_id', $adminId);
        }

        $paginated = $query->paginate(50);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $paginated,
            ]);
        }

        $logs = $paginated->through(fn ($l) => [
            'id' => $l->id,
            'admin_name' => $l->admin?->name ?? '-',
            'action' => $l->action,
            'description' => $l->description,
            'target_type' => $l->target_type,
            'target_id' => $l->target_id,
            'created_at' => $l->created_at?->format('d/m/Y H:i:s'),
        ]);

        $actions = AdminLog::distinct()->pluck('action')->sort()->values()->toArray();

        return view('admin.logs.index', compact('logs', 'actions'));
    }
}
