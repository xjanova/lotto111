<?php

namespace App\Events;

use App\Models\SmsPaymentNotification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event: SMS notification จับคู่กับ Deposit
 *
 * Broadcast ไปที่:
 * - Admin dashboard (real-time monitor)
 * - User (ถ้าจับคู่สำเร็จ)
 */
class DepositMatched implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly ?int $depositId,
        public readonly SmsPaymentNotification $notification,
        public readonly bool $success,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin.sms-deposits'),
        ];

        if ($this->depositId && $this->success) {
            // แจ้งเตือนไปที่ user ที่กำลังรอ
            $channels[] = new PrivateChannel('deposit.' . $this->depositId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'deposit.matched';
    }

    public function broadcastWith(): array
    {
        return [
            'deposit_id' => $this->depositId,
            'notification_id' => $this->notification->id,
            'bank' => $this->notification->bank,
            'amount' => $this->notification->amount,
            'success' => $this->success,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
