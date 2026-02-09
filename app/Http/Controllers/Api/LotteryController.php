<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lottery\PlaceBetRequest;
use App\Models\LotteryRound;
use App\Models\YeekeeSubmission;
use App\Services\BettingService;
use App\Services\LotteryService;
use App\Services\Scraper\ResultSourceManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LotteryController extends Controller
{
    public function __construct(
        private LotteryService $lotteryService,
        private BettingService $bettingService,
    ) {}

    /**
     * GET /api/lottery/types
     */
    public function types(): JsonResponse
    {
        $types = $this->lotteryService->getActiveTypes();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    /**
     * GET /api/lottery/rounds
     */
    public function rounds(Request $request): JsonResponse
    {
        $rounds = $this->lotteryService->getOpenRounds(
            $request->integer('lottery_type_id') ?: null
        );

        return response()->json([
            'success' => true,
            'data' => $rounds,
        ]);
    }

    /**
     * GET /api/lottery/rounds/{id}
     */
    public function roundDetail(int $id): JsonResponse
    {
        $round = $this->lotteryService->getRoundDetail($id);

        if (! $round) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบรอบหวย',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $round,
        ]);
    }

    /**
     * GET /api/lottery/rates/{roundId}
     */
    public function rates(int $roundId): JsonResponse
    {
        $round = \App\Models\LotteryRound::find($roundId);
        if (! $round) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบรอบหวย',
            ], 404);
        }

        $rates = $this->lotteryService->getRates($round->lottery_type_id);

        return response()->json([
            'success' => true,
            'data' => $rates,
        ]);
    }

    /**
     * POST /api/lottery/bet
     */
    public function placeBet(PlaceBetRequest $request): JsonResponse
    {
        $result = $this->bettingService->placeBet(
            $request->user(),
            $request->round_id,
            $request->bets,
        );

        return response()->json($result, $result['success'] ? 201 : 422);
    }

    /**
     * POST /api/lottery/yeekee/submit
     * ยิงเลข Yeekee
     */
    public function yeekeeSubmit(Request $request): JsonResponse
    {
        $request->validate([
            'round_id' => 'required|exists:lottery_rounds,id',
            'number' => 'required|string|regex:/^\d{5}$/',
        ]);

        $round = LotteryRound::with('lotteryType')->findOrFail($request->round_id);

        // ตรวจสอบว่าเป็นรอบ Yeekee
        if ($round->lotteryType?->slug !== 'yeekee') {
            return response()->json([
                'success' => false,
                'message' => 'รอบนี้ไม่ใช่ Yeekee',
            ], 422);
        }

        // ตรวจสอบว่ารอบเปิดอยู่
        if (! $round->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'รอบนี้ปิดรับแล้ว',
            ], 422);
        }

        $manager = app(ResultSourceManager::class);
        $submission = $manager->getYeekeeEngine()->submitNumber(
            $round,
            $request->user()->id,
            $request->number,
        );

        return response()->json([
            'success' => true,
            'message' => 'ส่งเลขสำเร็จ',
            'data' => [
                'submission_id' => $submission->id,
                'number' => $submission->number,
                'sequence' => $submission->sequence,
                'round_code' => $round->round_code,
            ],
        ], 201);
    }

    /**
     * GET /api/lottery/yeekee/submissions/{roundId}
     * ดูเลขที่ส่งมาแล้วในรอบ Yeekee
     */
    public function yeekeeSubmissions(int $roundId): JsonResponse
    {
        $round = LotteryRound::findOrFail($roundId);

        $submissions = YeekeeSubmission::where('lottery_round_id', $roundId)
            ->with('user:id,name')
            ->orderBy('sequence')
            ->get()
            ->map(fn ($s) => [
                'sequence' => $s->sequence,
                'number' => $s->number,
                'user' => $s->user?->name ?? 'Anonymous',
                'time' => $s->created_at?->format('H:i:s'),
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'round_code' => $round->round_code,
                'total' => $submissions->count(),
                'submissions' => $submissions,
            ],
        ]);
    }
}
