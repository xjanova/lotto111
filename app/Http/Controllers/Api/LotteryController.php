<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lottery\PlaceBetRequest;
use App\Services\BettingService;
use App\Services\LotteryService;
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
}
