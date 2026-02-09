<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\BettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(
        private BettingService $bettingService,
    ) {}

    /**
     * GET /api/tickets
     */
    public function index(Request $request): JsonResponse
    {
        $query = Ticket::where('user_id', $request->user()->id)
            ->with(['round.lotteryType', 'items.betType', 'betType'])
            ->orderBy('bet_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $tickets,
        ]);
    }

    /**
     * GET /api/tickets/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $ticket = Ticket::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with(['round.lotteryType', 'round.results', 'items.betType', 'betType'])
            ->first();

        if (! $ticket) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบโพย',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ticket,
        ]);
    }

    /**
     * GET /api/tickets/today
     */
    public function today(Request $request): JsonResponse
    {
        $tickets = Ticket::where('user_id', $request->user()->id)
            ->whereDate('bet_at', today())
            ->with(['round.lotteryType', 'items.betType', 'betType'])
            ->orderBy('bet_at', 'desc')
            ->get();

        $summary = [
            'total_tickets' => $tickets->count(),
            'total_amount' => $tickets->sum(fn ($t) => (float) ($t->total_amount ?: $t->amount)),
            'pending_count' => $tickets->where('status', 'pending')->count(),
            'won_count' => $tickets->where('status', 'won')->count(),
            'total_win' => $tickets->sum(fn ($t) => (float) ($t->total_win ?: $t->win_amount)),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'tickets' => $tickets,
            ],
        ]);
    }

    /**
     * POST /api/tickets/{id}/cancel
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $ticket = Ticket::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $result = $this->bettingService->cancelTicket($ticket);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * POST /api/tickets/reuse/{id}
     */
    public function reuse(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'round_id' => 'required|integer|exists:lottery_rounds,id',
        ]);

        $ticket = Ticket::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with('items')
            ->firstOrFail();

        $result = $this->bettingService->reuseTicket(
            $request->user(),
            $ticket,
            $request->round_id,
        );

        return response()->json($result, $result['success'] ? 201 : 422);
    }
}
