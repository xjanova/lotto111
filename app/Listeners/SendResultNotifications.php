<?php

namespace App\Listeners;

use App\Enums\TicketStatus;
use App\Events\ResultAnnounced;
use App\Models\Ticket;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendResultNotifications implements ShouldQueue
{
    public string $queue = 'low';

    public function __construct(
        private NotificationService $notificationService,
    ) {}

    public function handle(ResultAnnounced $event): void
    {
        $round = $event->round;

        // Notify winners
        $winners = Ticket::where('lottery_round_id', $round->id)
            ->where('status', TicketStatus::Won)
            ->with('user')
            ->get();

        foreach ($winners as $ticket) {
            $winAmount = (float) ($ticket->total_win ?: $ticket->win_amount);
            $this->notificationService->send(
                $ticket->user,
                'result',
                'ถูกรางวัล!',
                "โพย {$ticket->ticket_code} ถูกรางวัล {$winAmount} บาท",
                ['ticket_id' => $ticket->id, 'amount' => $winAmount],
            );
        }
    }
}
