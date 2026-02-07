<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RiskAlertCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $type,
        public readonly string $severity,
        public readonly string $title,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.risk-alerts'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'risk.alert';
    }
}
