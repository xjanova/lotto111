<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessLotteryResult;
use App\Models\AdminLog;
use App\Models\BetLimit;
use App\Models\BetType;
use App\Models\BetTypeRate;
use App\Models\LotteryRound;
use App\Models\LotteryType;
use App\Models\Ticket;
use App\Services\LotteryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LotteryManageController extends Controller
{
    public function __construct(
        private LotteryService $lotteryService,
    ) {}

    /**
     * GET /admin/lottery
     */
    public function index(Request $request): View|JsonResponse
    {
        $types = LotteryType::withCount(['rounds' => fn ($q) => $q->whereIn('status', ['upcoming', 'open'])])
            ->orderBy('sort_order')
            ->get();

        $openRounds = LotteryRound::with(['lotteryType', 'results'])
            ->whereIn('status', ['upcoming', 'open', 'closed', 'resulted'])
            ->orderBy('close_at', 'desc')
            ->limit(50)
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'types' => $types,
                    'open_rounds' => $openRounds,
                ],
            ]);
        }

        $lotteryTypes = $types->map(fn ($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'category' => $t->category ?? '',
            'slug' => $t->slug ?? '',
            'is_active' => (bool) $t->is_active,
            'open_rounds' => $t->rounds_count,
            'today_bets' => Ticket::whereHas('round', fn ($q) => $q->where('lottery_type_id', $t->id))
                ->whereDate('created_at', today())->sum('total_amount'),
        ])->toArray();

        $rounds = $openRounds->map(fn ($r) => [
            'id' => $r->id,
            'type_name' => $r->lotteryType?->name ?? '-',
            'round_code' => $r->round_code,
            'status' => $r->status?->value ?? $r->status,
            'open_at' => $r->open_at?->format('d/m/Y H:i'),
            'close_at' => $r->close_at?->format('d/m/Y H:i'),
            'result' => $r->hasResult() ? ($r->getResultValue('three_top') ?? 'ออกแล้ว') : null,
            'total_bets' => Ticket::where('lottery_round_id', $r->id)->sum('total_amount'),
        ])->toArray();

        return view('admin.lottery.index', compact('lotteryTypes', 'rounds'));
    }

    /**
     * GET /admin/lottery/types
     */
    public function types(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => LotteryType::orderBy('sort_order')->get(),
        ]);
    }

    /**
     * PUT /admin/lottery/types/{type}
     */
    public function updateType(Request $request, LotteryType $type): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:100',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
            'settings' => 'sometimes|array',
        ]);

        $type->update($request->only(['name', 'is_active', 'sort_order', 'settings']));
        $this->lotteryService->clearCache();

        AdminLog::log($request->user()->id, 'update_lottery_type', "อัปเดตประเภทหวย: {$type->name}", 'lottery_type', $type->id);

        return response()->json([
            'success' => true,
            'message' => 'อัปเดตสำเร็จ',
            'data' => $type,
        ]);
    }

    /**
     * GET /admin/lottery/rounds
     */
    public function rounds(Request $request): JsonResponse
    {
        $query = LotteryRound::with('lotteryType');

        if ($typeId = $request->integer('lottery_type_id')) {
            $query->where('lottery_type_id', $typeId);
        }

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('close_at', 'desc')->paginate(20),
        ]);
    }

    /**
     * POST /admin/lottery/rounds
     */
    public function createRound(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'lottery_type_id' => 'required|exists:lottery_types,id',
            'open_at' => 'required|date',
            'close_at' => 'required|date|after:open_at',
            'round_number' => 'nullable|integer',
            'round_code' => 'nullable|string|max:50',
        ]);

        $round = $this->lotteryService->createRound(
            $request->lottery_type_id,
            Carbon::parse($request->open_at),
            Carbon::parse($request->close_at),
            $request->round_number,
        );

        AdminLog::log($request->user()->id, 'create_round', "สร้างรอบหวย: {$round->round_code}", 'lottery_round', $round->id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'สร้างรอบสำเร็จ',
                'data' => $round->load('lotteryType'),
            ], 201);
        }

        return redirect()->route('admin.lottery.index')->with('success', 'สร้างรอบหวยสำเร็จ');
    }

    /**
     * PUT /admin/lottery/rounds/{round}
     */
    public function updateRound(Request $request, LotteryRound $round): JsonResponse
    {
        $request->validate([
            'status' => 'sometimes|string|in:upcoming,open,closed,cancelled',
            'close_at' => 'sometimes|date',
        ]);

        $round->update($request->only(['status', 'close_at']));
        $this->lotteryService->clearCache();

        return response()->json([
            'success' => true,
            'message' => 'อัปเดตรอบสำเร็จ',
            'data' => $round,
        ]);
    }

    /**
     * POST /admin/lottery/results/{round}
     */
    public function submitResult(Request $request, LotteryRound $round): JsonResponse
    {
        $request->validate([
            'results' => 'required|array',
            'results.three_top' => 'nullable|string|size:3',
            'results.two_bottom' => 'nullable|string|size:2',
            'results.three_bottom' => 'nullable|string|size:3',
        ]);

        // Process asynchronously for large rounds
        ProcessLotteryResult::dispatch($round, $request->results);

        AdminLog::log($request->user()->id, 'submit_result', "ออกผลรอบ: {$round->round_code}", 'lottery_round', $round->id);

        return response()->json([
            'success' => true,
            'message' => 'กำลังประมวลผลรางวัล',
        ]);
    }

    /**
     * GET /admin/lottery/rates
     */
    public function rates(Request $request): JsonResponse
    {
        $query = BetTypeRate::with(['lotteryType', 'betType']);

        if ($typeId = $request->integer('lottery_type_id')) {
            $query->where('lottery_type_id', $typeId);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    /**
     * PUT /admin/lottery/rates/{rate}
     */
    public function updateRate(Request $request, BetTypeRate $rate): JsonResponse
    {
        $request->validate([
            'rate' => 'sometimes|numeric|min:0',
            'min_amount' => 'sometimes|numeric|min:0',
            'max_amount' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $rate->update($request->only(['rate', 'min_amount', 'max_amount', 'is_active']));
        $this->lotteryService->clearCache();

        AdminLog::log($request->user()->id, 'update_rate', "อัปเดตอัตราจ่าย: {$rate->betType?->name} = {$rate->rate}", 'bet_type_rate', $rate->id);

        return response()->json([
            'success' => true,
            'message' => 'อัปเดตอัตราจ่ายสำเร็จ',
        ]);
    }

    /**
     * GET /admin/lottery/limits
     */
    public function limits(Request $request): JsonResponse
    {
        $query = BetLimit::with(['round.lotteryType', 'betType']);

        if ($roundId = $request->integer('round_id')) {
            $query->where('lottery_round_id', $roundId);
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('created_at', 'desc')->paginate(50),
        ]);
    }

    /**
     * POST /admin/lottery/limits
     */
    public function createLimit(Request $request): JsonResponse
    {
        $request->validate([
            'lottery_round_id' => 'required|exists:lottery_rounds,id',
            'bet_type_id' => 'required|exists:bet_types,id',
            'number' => 'required|string|regex:/^[0-9]+$/',
            'max_amount' => 'required|numeric|min:0',
        ]);

        $limit = BetLimit::updateOrCreate(
            [
                'lottery_round_id' => $request->lottery_round_id,
                'bet_type_id' => $request->bet_type_id,
                'number' => $request->number,
            ],
            ['max_amount' => $request->max_amount]
        );

        AdminLog::log(
            $request->user()->id,
            'create_limit',
            "ตั้งเลขอั้น: {$request->number} สูงสุด {$request->max_amount} บาท",
            'bet_limit',
            $limit->id,
        );

        return response()->json([
            'success' => true,
            'message' => $request->max_amount == 0 ? 'อั้นเลขสำเร็จ' : 'จำกัดวงเงินสำเร็จ',
            'data' => $limit,
        ], 201);
    }
}
