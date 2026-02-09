<?php

namespace App\Events;

use App\Models\LotteryRound;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResultAnnounced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public LotteryRound $round,
        public array $results,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('results'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'result.announced';
    }
}
