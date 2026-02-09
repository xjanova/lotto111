<?php

namespace App\Listeners;

use App\Events\BetPlaced;
use App\Services\GamificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateGamificationOnBet implements ShouldQueue
{
    public string $queue = 'low';

    public function __construct(
        private GamificationService $gamificationService,
    ) {}

    public function handle(BetPlaced $event): void
    {
        $ticket = $event->ticket;
        $amount = (float) ($ticket->total_amount ?: $ticket->amount);

        // Award XP for betting
        $xp = max(1, (int) ($amount / 10));
        $this->gamificationService->awardXp($ticket->user, $xp, 'bet');
    }
}
