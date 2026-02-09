<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ResultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function __construct(
        private ResultService $resultService,
    ) {}

    /**
     * GET /api/results
     */
    public function index(): JsonResponse
    {
        $results = $this->resultService->getLatestResults();

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * GET /api/results/{date}
     */
    public function byDate(string $date): JsonResponse
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json([
                'success' => false,
                'message' => 'รูปแบบวันที่ไม่ถูกต้อง (YYYY-MM-DD)',
            ], 422);
        }

        $results = $this->resultService->getResultsByDate($date);

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * GET /api/results/type/{typeId}
     */
    public function byType(int $typeId, Request $request): JsonResponse
    {
        $results = $this->resultService->getResultsByType(
            $typeId,
            $request->integer('limit', 20),
        );

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }
}
