<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RiskAlert;
use App\Models\Transaction;
use App\Services\Risk\RiskEngineService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Admin Controller สำหรับ Risk Management & Profit Control
 *
 * Features:
 * - Real-time dashboard (P&L, win rates, exposure)
 * - User risk profiles management
 * - Global settings control
 * - AI auto-balance trigger
 * - Reports & Analytics
 * - Alert management
 */
class RiskControlController extends Controller
{
    public function __construct(
        private RiskEngineService $riskEngine,
    ) {}

    // ─────────────────────────────────────────
    // Dashboard
    // ─────────────────────────────────────────

    /**
     * Risk Management Dashboard
     */
    public function dashboard(Request $request): View|JsonResponse
    {
        $data = $this->riskEngine->getLiveDashboard();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        }

        $betsToday = abs(Transaction::where('type', 'bet')->whereDate('created_at', today())->sum('amount'));
        $winsToday = Transaction::where('type', 'win')->whereDate('created_at', today())->sum('amount');
        $profitToday = $betsToday - $winsToday;

        $activeAlerts = DB::table('risk_alerts')->where('status', 'new')->count();

        $riskStats = [
            'profit_today' => $profitToday,
            'total_exposure' => $data['open_exposure'] ?? 0,
            'player_win_rate' => $betsToday > 0 ? round($winsToday / $betsToday * 100, 1) : 0,
            'active_alerts' => $activeAlerts,
        ];

        $topWinners = Transaction::where('type', 'win')
            ->whereDate('created_at', today())
            ->selectRaw('user_id, SUM(amount) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($t) => [
                'name' => User::find($t->user_id)?->name ?? '-',
                'amount' => (float) $t->total,
            ])->toArray();

        // Get number exposure from open rounds
        $openRoundIds = \App\Models\LotteryRound::whereIn('status', ['open', 'closed'])->pluck('id');
        $numberExposure = [];
        if ($openRoundIds->isNotEmpty()) {
            $numberExposure = DB::table('number_exposures')
                ->whereIn('lottery_round_id', $openRoundIds)
                ->orderByDesc('potential_payout')
                ->limit(10)
                ->get()
                ->map(fn ($n) => [
                    'number' => $n->number ?? '-',
                    'bet_type' => $n->bet_type ?? '',
                    'total_amount' => (float) ($n->total_bet_amount ?? $n->potential_payout ?? 0),
                ])->toArray();
        }

        return view('admin.risk.dashboard', compact('riskStats', 'topWinners', 'numberExposure'));
    }

    /**
     * Live stats (poll every 10 seconds)
     */
    public function liveStats(): JsonResponse
    {
        $stats = $this->riskEngine->getCurrentMargin();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    // ─────────────────────────────────────────
    // User Risk Profiles
    // ─────────────────────────────────────────

    /**
     * รายชื่อ user พร้อม risk profile (sortable)
     */
    public function userProfiles(Request $request): JsonResponse
    {
        $data = $this->riskEngine->getUserProfitabilityRanking(
            sortBy: $request->input('sort', 'net_profit_for_system'),
            direction: $request->input('dir', 'desc'),
            perPage: (int) $request->input('per_page', 30),
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * รายละเอียด risk profile ของ user
     */
    public function userProfile(User $user): JsonResponse
    {
        $profile = $this->riskEngine->getUserRiskProfile($user);
        $riskLevel = $this->riskEngine->recalculateUserRisk($user);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'balance' => $user->balance,
                ],
                'risk_profile' => $profile,
                'calculated_risk_level' => $riskLevel->value,
            ],
        ]);
    }

    /**
     * ตั้งค่า Win Rate Override ของ user
     */
    public function setWinRate(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'win_rate' => 'nullable|numeric|min:0|max:100',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $this->riskEngine->setUserWinRateOverride(
            user: $user,
            winRate: $request->input('win_rate'),
            adminId: $request->user()->id,
            reason: $request->input('reason'),
        );

        return response()->json([
            'success' => true,
            'message' => 'อัพเดท Win Rate สำหรับ ' . $user->username . ' เรียบร้อย',
        ]);
    }

    /**
     * ตั้งค่า Rate Adjustment ของ user
     */
    public function setRateAdjustment(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'adjustment_percent' => 'required|numeric|min:-50|max:50',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $this->riskEngine->setUserRateAdjustment(
            user: $user,
            adjustmentPercent: $request->input('adjustment_percent'),
            adminId: $request->user()->id,
            reason: $request->input('reason'),
        );

        return response()->json([
            'success' => true,
            'message' => 'อัพเดท Rate Adjustment สำหรับ ' . $user->username . ' เรียบร้อย',
        ]);
    }

    /**
     * ตั้งค่า Blocked Numbers ของ user
     */
    public function setBlockedNumbers(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'blocked_numbers' => 'required|array',
            'blocked_numbers.*' => 'string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $this->riskEngine->setUserBlockedNumbers(
            user: $user,
            numbers: $request->input('blocked_numbers'),
            adminId: $request->user()->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'อัพเดทเลขอั้นสำหรับ ' . $user->username . ' เรียบร้อย',
        ]);
    }

    /**
     * ตั้งค่า Bet Limits ของ user
     */
    public function setBetLimits(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'max_bet_per_ticket' => 'nullable|numeric|min:0',
            'max_bet_per_number' => 'nullable|numeric|min:0',
            'max_payout_per_day' => 'nullable|numeric|min:0',
            'max_payout_per_ticket' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $this->riskEngine->setUserBetLimits(
            user: $user,
            limits: $request->only([
                'max_bet_per_ticket',
                'max_bet_per_number',
                'max_payout_per_day',
                'max_payout_per_ticket',
            ]),
            adminId: $request->user()->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'อัพเดท Bet Limits สำหรับ ' . $user->username . ' เรียบร้อย',
        ]);
    }

    // ─────────────────────────────────────────
    // Global Settings
    // ─────────────────────────────────────────

    /**
     * ดึง Risk Settings ทั้งหมด
     */
    public function settings(): JsonResponse
    {
        $settings = DB::table('risk_settings')
            ->get()
            ->keyBy('key')
            ->map(function ($item) {
                return [
                    'key' => $item->key,
                    'value' => $item->value,
                    'data_type' => $item->data_type,
                    'description' => $item->description,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * อัพเดท Risk Settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        foreach ($request->input('settings') as $setting) {
            DB::table('risk_settings')
                ->where('key', $setting['key'])
                ->update([
                    'value' => $setting['value'],
                    'updated_by' => $request->user()->id,
                    'updated_at' => now(),
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'อัพเดทการตั้งค่าเรียบร้อย',
        ]);
    }

    // ─────────────────────────────────────────
    // AI Auto-Balance
    // ─────────────────────────────────────────

    /**
     * Trigger AI auto-balance ด้วยมือ
     */
    public function runAutoBalance(): JsonResponse
    {
        $this->riskEngine->runAutoBalance();

        return response()->json([
            'success' => true,
            'message' => 'AI Auto-Balance ทำงานเรียบร้อย',
        ]);
    }

    // ─────────────────────────────────────────
    // Reports
    // ─────────────────────────────────────────

    /**
     * Top Winners
     */
    public function topWinners(Request $request): JsonResponse
    {
        $period = $request->input('period', 'today');
        $limit = (int) $request->input('limit', 20);

        $data = $this->riskEngine->getTopWinners($period, $limit);

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Top Losers (system profit)
     */
    public function topLosers(Request $request): JsonResponse
    {
        $period = $request->input('period', 'today');
        $limit = (int) $request->input('limit', 20);

        $data = $this->riskEngine->getTopLosers($period, $limit);

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Number Exposure (เลขที่มีความเสี่ยง)
     */
    public function numberExposure(Request $request): JsonResponse
    {
        $roundId = (int) $request->input('round_id', 0);
        $limit = (int) $request->input('limit', 50);

        $data = $this->riskEngine->getTopExposedNumbers($roundId, $limit);

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Profit Snapshots
     */
    public function profitSnapshots(Request $request): JsonResponse
    {
        $type = $request->input('type', 'daily');
        $limit = (int) $request->input('limit', 30);

        $data = DB::table('profit_snapshots')
            ->where('period_type', $type)
            ->orderBy('period_start', 'desc')
            ->limit($limit)
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    // ─────────────────────────────────────────
    // Alerts
    // ─────────────────────────────────────────

    /**
     * Risk Alerts
     */
    public function alerts(Request $request): JsonResponse
    {
        $query = DB::table('risk_alerts')
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        if ($request->filled('type')) {
            $query->where('alert_type', $request->input('type'));
        }

        $alerts = $query->paginate($request->input('per_page', 30));

        return response()->json(['success' => true, 'data' => $alerts]);
    }

    /**
     * Acknowledge alert
     */
    public function acknowledgeAlert(Request $request, int $alert): JsonResponse
    {
        DB::table('risk_alerts')
            ->where('id', $alert)
            ->update([
                'status' => 'acknowledged',
                'acknowledged_by' => $request->user()->id,
                'acknowledged_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'รับทราบแจ้งเตือนแล้ว']);
    }

    /**
     * Resolve alert
     */
    public function resolveAlert(Request $request, int $alert): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'note' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::table('risk_alerts')
            ->where('id', $alert)
            ->update([
                'status' => 'resolved',
                'resolved_by' => $request->user()->id,
                'resolved_at' => now(),
                'resolution_note' => $request->input('note'),
            ]);

        return response()->json(['success' => true, 'message' => 'แก้ไขปัญหาเรียบร้อย']);
    }

    // ─────────────────────────────────────────
    // Adjustment Logs
    // ─────────────────────────────────────────

    /**
     * Rate Adjustment Logs
     */
    public function adjustmentLogs(Request $request): JsonResponse
    {
        $query = DB::table('rate_adjustments_log')
            ->orderBy('created_at', 'desc');

        if ($request->filled('target_type')) {
            $query->where('target_type', $request->input('target_type'));
        }

        if ($request->filled('adjusted_by')) {
            $query->where('adjusted_by', $request->input('adjusted_by'));
        }

        $logs = $query->paginate($request->input('per_page', 50));

        return response()->json(['success' => true, 'data' => $logs]);
    }
}
