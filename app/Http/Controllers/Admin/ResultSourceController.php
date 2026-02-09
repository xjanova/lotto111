<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessLotteryResult;
use App\Models\AdminLog;
use App\Models\LotteryRound;
use App\Models\ResultFetchLog;
use App\Models\ResultSource;
use App\Services\Scraper\ResultSourceManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin API สำหรับจัดการระบบดึงผลหวย
 *
 * Features:
 *   - ดู/แก้ไข Result Sources
 *   - สลับ mode (auto ↔ manual)
 *   - ทดสอบ scrape (dry run)
 *   - สั่ง fetch ทันที
 *   - ดู health check
 *   - ดูประวัติ fetch logs
 *   - กรอกผลด้วยมือ (manual submit)
 *   - จัดการ Yeekee
 */
class ResultSourceController extends Controller
{
    public function __construct(
        private ResultSourceManager $manager,
    ) {}

    /**
     * GET /admin/result-sources
     * ดู source ทั้งหมดพร้อมสถานะ
     */
    public function index(Request $request): View|JsonResponse
    {
        $sources = ResultSource::with('lotteryType')
            ->orderBy('lottery_type_id')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $sources,
            ]);
        }

        $totalSources = $sources->count();
        $autoCount = $sources->where('mode', 'auto')->count();
        $manualCount = $sources->where('mode', 'manual')->count();
        $fetchCount24h = ResultFetchLog::where('fetched_at', '>=', now()->subDay())->count();

        $sourcesArray = $sources->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'provider' => $s->provider,
            'lottery_type_name' => $s->lotteryType?->name ?? '-',
            'mode' => $s->mode,
            'source_url' => $s->source_url,
            'is_active' => (bool) $s->is_active,
            'last_status' => $s->last_status,
            'last_fetched_at' => $s->last_fetched_at?->format('d/m/Y H:i') ?? null,
        ])->toArray();

        $closedRounds = LotteryRound::with('lotteryType')
            ->where('status', 'closed')
            ->whereDoesntHave('results')
            ->orderBy('close_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'type_name' => $r->lotteryType?->name ?? '-',
                'round_code' => $r->round_code,
            ])->toArray();

        return view('admin.result-sources.index', [
            'sources' => $sourcesArray,
            'totalSources' => $totalSources,
            'autoCount' => $autoCount,
            'manualCount' => $manualCount,
            'fetchCount24h' => $fetchCount24h,
            'closedRounds' => $closedRounds,
        ]);
    }

    /**
     * GET /admin/result-sources/status
     * สรุปสถานะระบบทั้งหมด
     */
    public function systemStatus(): JsonResponse
    {
        $status = $this->manager->getSystemStatus();

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    /**
     * GET /admin/result-sources/{id}
     * ดูรายละเอียด source พร้อม logs ล่าสุด
     */
    public function show(int $id): JsonResponse
    {
        $source = ResultSource::with('lotteryType')->findOrFail($id);
        $recentLogs = ResultFetchLog::where('result_source_id', $id)
            ->with('round')
            ->latest('fetched_at')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'source' => $source,
                'recent_logs' => $recentLogs,
            ],
        ]);
    }

    /**
     * POST /admin/result-sources
     * สร้าง source ใหม่
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lottery_type_id' => 'required|exists:lottery_types,id',
            'provider' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'mode' => 'required|in:auto,manual',
            'source_url' => 'nullable|url|max:500',
            'fallback_url' => 'nullable|url|max:500',
            'scrape_config' => 'nullable|array',
            'schedule' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
            'priority' => 'sometimes|integer|min:0',
            'retry_count' => 'sometimes|integer|min:0|max:10',
            'retry_delay_seconds' => 'sometimes|integer|min:5|max:300',
            'timeout_seconds' => 'sometimes|integer|min:5|max:120',
        ]);

        $source = ResultSource::create($validated);

        AdminLog::log(
            $request->user()->id,
            'create_result_source',
            "สร้างแหล่งผลหวย: {$source->name} (mode: {$source->mode})",
            'result_source',
            $source->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'สร้างแหล่งข้อมูลสำเร็จ',
            'data' => $source->load('lotteryType'),
        ], 201);
    }

    /**
     * PUT /admin/result-sources/{id}
     * แก้ไข source
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $source = ResultSource::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'source_url' => 'nullable|url|max:500',
            'fallback_url' => 'nullable|url|max:500',
            'scrape_config' => 'nullable|array',
            'schedule' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
            'priority' => 'sometimes|integer|min:0',
            'retry_count' => 'sometimes|integer|min:0|max:10',
            'retry_delay_seconds' => 'sometimes|integer|min:5|max:300',
            'timeout_seconds' => 'sometimes|integer|min:5|max:120',
        ]);

        $source->update($validated);

        AdminLog::log(
            $request->user()->id,
            'update_result_source',
            "แก้ไขแหล่งผลหวย: {$source->name}",
            'result_source',
            $source->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'อัปเดตสำเร็จ',
            'data' => $source->fresh()->load('lotteryType'),
        ]);
    }

    /**
     * PUT /admin/result-sources/{id}/mode
     * สลับ mode (auto ↔ manual)
     */
    public function switchMode(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'mode' => 'required|in:auto,manual',
        ]);

        $source = ResultSource::findOrFail($id);
        $updated = $this->manager->switchMode($source, $request->mode, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => "เปลี่ยนเป็น mode: {$request->mode} สำเร็จ",
            'data' => $updated->load('lotteryType'),
        ]);
    }

    /**
     * POST /admin/result-sources/{id}/fetch
     * สั่ง fetch ผลทันที
     */
    public function fetchNow(Request $request, int $id): JsonResponse
    {
        $source = ResultSource::findOrFail($id);
        $date = $request->input('date');

        $result = $this->manager->fetchFromSource($source, $date);

        AdminLog::log(
            $request->user()->id,
            'manual_fetch_result',
            "สั่งดึงผลจาก: {$source->name}" . ($result->success ? ' (สำเร็จ)' : ' (ล้มเหลว)'),
            'result_source',
            $source->id,
        );

        return response()->json([
            'success' => $result->success,
            'message' => $result->success ? 'ดึงผลสำเร็จ' : "ดึงผลล้มเหลว: {$result->error}",
            'data' => [
                'results' => $result->results,
                'draw_date' => $result->drawDate,
                'response_time_ms' => $result->responseTimeMs,
                'source_url' => $result->sourceUrl,
            ],
        ]);
    }

    /**
     * POST /admin/result-sources/{id}/test
     * ทดสอบ scrape (dry run - ไม่ submit ผลจริง)
     */
    public function testScrape(Request $request, int $id): JsonResponse
    {
        $source = ResultSource::findOrFail($id);
        $date = $request->input('date');

        $result = $this->manager->testScrape($source, $date);

        return response()->json([
            'success' => $result->success,
            'message' => $result->success ? 'ทดสอบ scrape สำเร็จ' : "ทดสอบล้มเหลว: {$result->error}",
            'data' => [
                'results' => $result->results,
                'raw_data' => $result->rawData,
                'draw_date' => $result->drawDate,
                'response_time_ms' => $result->responseTimeMs,
                'source_url' => $result->sourceUrl,
            ],
        ]);
    }

    /**
     * GET /admin/result-sources/health
     * Health check ทุก source
     */
    public function healthCheck(): JsonResponse
    {
        $results = $this->manager->healthCheckAll();

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * POST /admin/result-sources/fetch-all
     * สั่ง fetch ทุก source ที่ active
     */
    public function fetchAll(Request $request): JsonResponse
    {
        $date = $request->input('date');
        $results = $this->manager->fetchAllActive($date);

        $success = collect($results)->where('success', true)->count();
        $failed = collect($results)->where('success', false)->count();

        AdminLog::log(
            $request->user()->id,
            'fetch_all_results',
            "สั่งดึงผลทั้งหมด: สำเร็จ {$success}, ล้มเหลว {$failed}",
        );

        return response()->json([
            'success' => true,
            'message' => "ดึงผลครบ: สำเร็จ {$success}, ล้มเหลว {$failed}",
            'data' => $results,
        ]);
    }

    /**
     * POST /admin/result-sources/manual-submit
     * กรอกผลด้วยมือ (manual mode)
     */
    public function manualSubmit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lottery_round_id' => 'required|exists:lottery_rounds,id',
            'results' => 'required|array',
            'results.three_top' => 'nullable|string|size:3|regex:/^\d{3}$/',
            'results.two_top' => 'nullable|string|size:2|regex:/^\d{2}$/',
            'results.two_bottom' => 'nullable|string|size:2|regex:/^\d{2}$/',
            'results.three_bottom' => 'nullable|string|size:3|regex:/^\d{3}$/',
            'results.four_top' => 'nullable|string|size:4|regex:/^\d{4}$/',
            'results.first_prize' => 'nullable|string|regex:/^\d+$/',
        ]);

        $round = LotteryRound::findOrFail($validated['lottery_round_id']);

        if ($round->hasResult()) {
            return response()->json([
                'success' => false,
                'message' => 'รอบนี้ออกผลไปแล้ว',
            ], 422);
        }

        // Log manual submit
        ResultFetchLog::logFetch(
            sourceId: ResultSource::where('lottery_type_id', $round->lottery_type_id)->first()?->id ?? 0,
            status: 'success',
            parsedResults: $validated['results'],
            roundId: $round->id,
        );

        // Process result
        ProcessLotteryResult::dispatch($round, $validated['results']);

        AdminLog::log(
            $request->user()->id,
            'manual_submit_result',
            "กรอกผลด้วยมือรอบ: {$round->round_code} - " . json_encode($validated['results']),
            'lottery_round',
            $round->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'กรอกผลสำเร็จ กำลังประมวลผลรางวัล',
            'data' => [
                'round' => $round->round_code,
                'results' => $validated['results'],
            ],
        ]);
    }

    /**
     * GET /admin/result-sources/{id}/logs
     * ดูประวัติ fetch logs ของ source
     */
    public function logs(Request $request, int $id): JsonResponse
    {
        $query = ResultFetchLog::where('result_source_id', $id)
            ->with('round');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest('fetched_at')->paginate(30),
        ]);
    }

    /**
     * DELETE /admin/result-sources/{id}
     * ลบ source
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $source = ResultSource::findOrFail($id);
        $name = $source->name;
        $source->delete();

        AdminLog::log(
            $request->user()->id,
            'delete_result_source',
            "ลบแหล่งผลหวย: {$name}",
            'result_source',
            $id,
        );

        return response()->json([
            'success' => true,
            'message' => 'ลบสำเร็จ',
        ]);
    }

    /**
     * GET /admin/result-sources/providers
     * ดู providers ที่ลงทะเบียน
     */
    public function providers(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->manager->getRegisteredProviders(),
        ]);
    }

    /**
     * POST /admin/result-sources/yeekee/calculate
     * สั่งคำนวณผล Yeekee ของรอบที่ระบุ
     */
    public function calculateYeekee(Request $request): JsonResponse
    {
        $request->validate([
            'round_id' => 'required|exists:lottery_rounds,id',
        ]);

        $round = LotteryRound::findOrFail($request->round_id);

        $result = $this->manager->calculateYeekeeResult($round);

        if (! $result->success) {
            return response()->json([
                'success' => false,
                'message' => "คำนวณล้มเหลว: {$result->error}",
            ], 422);
        }

        // ส่งไป process
        ProcessLotteryResult::dispatch($round, $result->results);

        AdminLog::log(
            $request->user()->id,
            'calculate_yeekee',
            "คำนวณ Yeekee รอบ: {$round->round_code}",
            'lottery_round',
            $round->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'คำนวณผล Yeekee สำเร็จ',
            'data' => [
                'round' => $round->round_code,
                'results' => $result->results,
                'raw_data' => $result->rawData,
            ],
        ]);
    }

    /**
     * GET /admin/result-sources/yeekee/submissions/{roundId}
     * ดูเลขที่ user ส่งมาในรอบ Yeekee
     */
    public function yeekeeSubmissions(int $roundId): JsonResponse
    {
        $round = LotteryRound::findOrFail($roundId);
        $submissions = $this->manager->getYeekeeEngine()->getSubmissions($round);

        return response()->json([
            'success' => true,
            'data' => [
                'round' => $round->round_code,
                'total_submissions' => count($submissions),
                'submissions' => $submissions,
            ],
        ]);
    }
}
